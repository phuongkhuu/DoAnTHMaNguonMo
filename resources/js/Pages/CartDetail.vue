<template>
  <div class="cart-page">
    <header class="cart-header">
      <h1>Giỏ hàng của bạn</h1>
      <p class="subtitle" v-if="user">Xin chào, <strong>{{ user.name }}</strong></p>
      <p class="subtitle" v-else>Vui lòng <a href="/login">đăng nhập</a> để quản lý giỏ hàng</p>
    </header>

    <main v-if="user" class="cart-main">
      <section v-if="loading" class="loading">Đang tải giỏ hàng…</section>

      <section v-else>
        <div v-if="cartData.length === 0" class="empty">
          <p>Giỏ hàng của bạn đang trống.</p>
          <a class="btn-primary" href="/shop">Tiếp tục mua sắm</a>
        </div>

        <div v-else class="cart-content">
          <div class="cart-list">
            <div v-for="item in cartData" :key="item.id" class="cart-row">
              <div class="row-left">
                <img :src="item.image" :alt="item.name || 'Sản phẩm'" class="thumb" @error="onImageError($event, item)" />
                <div class="meta">
                  <div class="name">{{ item.name }}</div>
                  <div class="meta-sub">
                    <span class="sku" v-if="item.sku">Mã: {{ item.sku }}</span>
                    <span class="price">{{ formatPrice(item.price) }}</span>
                  </div>
                </div>
              </div>

              <div class="row-right">
                <div class="qty-controls">
                  <button class="qty-btn" @click="changeQty(item, item.quantity - 1)">−</button>
                  <input class="qty-input" type="number" min="1" v-model.number="item.quantityLocal" @change="onQtyInput(item)" />
                  <button class="qty-btn" @click="changeQty(item, item.quantity + 1)">+</button>
                </div>

                <div class="line-total">{{ formatPrice(item.price) }}</div>

                <div class="row-actions">
                  <button class="btn-link" @click="removeItem(item)">Xóa</button>
                </div>
              </div>
            </div>
          </div>

          <aside class="cart-summary">
            <h2>Tóm tắt đơn hàng</h2>

            <div class="summary-row">
              <span>Số lượng</span>
              <span>{{ totalQuantityValue }}</span>
            </div>

            <div class="summary-row">
              <span>Tạm tính</span>
              <span class="summary-price">{{ formatPrice(cartSubtotalValue) }}</span>
            </div>

            <div class="summary-row">
              <span>Phí vận chuyển</span>
              <span class="muted">Tính khi thanh toán</span>
            </div>

            <div class="summary-row total">
              <span>Tổng</span>
              <span class="summary-price">{{ formatPrice(cartSubtotalValue) }}</span>
            </div>

            <div class="summary-actions">
              <button class="btn-primary full" @click="goToCheckout">Thanh toán</button>
              <a class="btn-secondary full" href="/shop">Tiếp tục mua sắm</a>
            </div>

            <div class="note" v-if="note">{{ note }}</div>
          </aside>
        </div>
      </section>
    </main>

    <main v-else class="not-logged">
      <p>Bạn cần đăng nhập để xem giỏ hàng.</p>
      <a class="btn-primary" href="/login">Đăng nhập</a>
    </main>

    <!-- QR Modal -->
    <div v-if="showQr" class="qr-modal" @click.self="closeQr">
      <div class="qr-card">
        <h3>Quét mã MoMo để thanh toán</h3>

        <p class="qr-sub">
          Số: <strong>{{ momoReceiver }}</strong> ·
          Số tiền:
          <strong>{{ formatPrice(qrAmount || cartSubtotalValue) }}</strong>
        </p>

        <img v-if="qrImage" :src="qrImage" alt="MoMo QR" class="qr-img" />
        <p v-else class="qr-help">Đang tạo mã…</p>

        <p class="qr-help">Mở ứng dụng MoMo → Quét mã → Xác nhận chuyển tiền</p>

        <div class="qr-actions">
          <button class="btn-primary" @click="copyMomoUri">Sao chép liên kết</button>
          <button class="btn-secondary" @click="closeQr">Đóng</button>
        </div>

        <div class="qr-actions">
          <button class="btn-primary" @click="confirmPayment">Xác nhận</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import QRCode from 'qrcode';

export default {
  name: 'CartDetail',
  data() {
    return {
      cartData: [],
      loading: true,
      placeholderImage: '/image/placeholder.png',
      receiptId: null,

      // Totals that DO NOT auto-update
      cartSubtotalValue: 0,
      totalQuantityValue: 0,

      // QR related
      qrImage: null,
      showQr: false,
      momoReceiver: '0907139274',
      momoUri: null,
      qrAmount: null,
      note: '',
    };
  },

  computed: {
    user() {
      return (this.$page && this.$page.props?.auth?.user)
        ? this.$page.props.auth.user
        : null;
    }
  },

  methods: {
    formatPrice(value) {
      if (typeof value !== 'number') return value;
      return value.toLocaleString('vi-VN') + '₫';
    },

    // ✅ Recalculate totals ONLY when fetching cart (page reload)
    updateTotals() {
      this.cartSubtotalValue = this.cartData.reduce(
        (sum, it) => sum + (it.price || 0) * (it.quantity || 0),
        0
      );

      this.totalQuantityValue = this.cartData.reduce(
        (sum, it) => sum + (it.quantity || 0),
        0
      );
    },

    async fetchCart() {
      if (!this.user) {
        this.loading = false;
        return;
      }
      this.loading = true;

      try {
        const res = await axios.get('/receipts/active');

        if (res.data) {
          this.receiptId = res.data.id;
          this.cartData = (res.data.items || []).map(i => ({
            ...i,
            quantity: i.quantity ?? 1,
            quantityLocal: i.quantity ?? 1,
          }));
        } else {
          this.cartData = [];
        }

        // ✅ Only update totals here
        //this.updateTotals();

      } catch (err) {
        console.error('fetchCart error', err);
        if (err.response?.status === 401) {
          window.location.href = '/login';
          return;
        }
        this.cartData = [];
      } finally {
        this.loading = false;
      }
    },

    async changeQty(item, newQty) {
      if (!this.user) {
        window.location.href = '/login';
        return;
      }

      newQty = Math.max(1, parseInt(newQty || 1, 10));

      try {
        await axios.put(`/user/cart/${item.id}`, { quantity: newQty });
        await this.fetchCart(); // ✅ totals update ONLY here
      } catch (err) {
        console.error('changeQty error', err);
        if (err.response?.status === 401) {
          window.location.href = '/login';
          return;
        }
        item.quantityLocal = item.quantity;
      }
    },

    onQtyInput(item) {
      const q = parseInt(item.quantityLocal || 1, 10);
      if (isNaN(q) || q < 1) {
        item.quantityLocal = item.quantity;
        return;
      }
      this.changeQty(item, q);
    },

    async removeItem(item) {
      if (!this.user) {
        window.location.href = '/login';
        return;
      }
      if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) return;

      try {
        await axios.delete(`/user/cart/${item.id}`);
        await this.fetchCart(); // ✅ totals update ONLY here
      } catch (err) {
        console.error('removeItem error', err);
        if (err.response?.status === 401) {
          window.location.href = '/login';
          return;
        }
      }
    },

    async goToCheckout() {
      if (!this.receiptId) {
        alert('Không tìm thấy hóa đơn để thanh toán');
        return;
      }

      const subtotal = Math.max(0, Math.round(this.cartSubtotalValue));
      if (subtotal <= 0) {
        alert('Giỏ hàng trống hoặc tổng tiền không hợp lệ');
        return;
      }

      try {
        const res = await axios.post('/receipts/checkout-momo', { receipt_id: this.receiptId });
        if (res.data?.momoUri) {
          this.momoUri = res.data.momoUri;
          this.qrAmount = res.data.amount ?? subtotal;

          try {
            const params = new URLSearchParams(this.momoUri.split('?')[1] || '');
            const r = params.get('receiver');
            if (r) this.momoReceiver = r;
          } catch {}
        } else {
          this.momoUri = this.buildLocalMomoUri(subtotal);
          this.qrAmount = subtotal;
        }
      } catch (err) {
        console.warn('Backend momoUri request failed, falling back to local URI', err);
        this.momoUri = this.buildLocalMomoUri(subtotal);
        this.qrAmount = subtotal;
      }

      if (!this.momoUri) {
        alert('Không thể tạo liên kết thanh toán MoMo');
        return;
      }

      try {
        this.qrImage = await QRCode.toDataURL(this.momoUri, {
          width: 320,
          margin: 2,
          errorCorrectionLevel: 'M',
        });
        this.showQr = true;
        this.note = `Quét mã bằng ứng dụng MoMo để chuyển ${this.formatPrice(this.qrAmount)} vào số ${this.momoReceiver}.`;
      } catch (err) {
        console.error('QR generation error', err);
        alert('Không thể tạo mã QR. Vui lòng thử lại.');
      }
    },

    async confirmPayment() {
      if (!this.receiptId) {
        alert('Không tìm thấy hóa đơn để thanh toán');
        return;
      }
      try {
        await axios.post('/receipts/checkout', { receipt_id: this.receiptId });
        this.closeQr();
        await this.fetchCart();
        alert('Đã xác nhận thanh toán và tạo hóa đơn mới');
      } catch (err) {
        console.error('checkout error', err);
        alert('Thanh toán thất bại');
      }
    },

    buildLocalMomoUri(amount) {
      if (!/^\d{9,11}$/.test(this.momoReceiver)) {
        alert('Số điện thoại MoMo không hợp lệ.');
        return null;
      }
      const comment = `Thanh toán hóa đơn #${this.receiptId}`;
      const params = new URLSearchParams({
        action: 'pay',
        receiver: this.momoReceiver,
        amount: String(amount),
        comment,
      });
      return `momo://?${params.toString()}`;
    },

    closeQr() {
      this.showQr = false;
      this.qrImage = null;
      this.momoUri = null;
      this.qrAmount = null;
      this.note = '';
    },

    copyMomoUri() {
      if (!this.momoUri) {
        alert('Không có liên kết để sao chép');
        return;
      }
      navigator.clipboard?.writeText(this.momoUri)
        .then(() => alert('Đã sao chép liên kết MoMo'))
        .catch(() => alert('Không thể sao chép.'));
    },

    onImageError(event) {
      event.target.src = this.placeholderImage;
    }
  },

  mounted() {
    this.fetchCart(); // ✅ totals update ONLY here
  }
};
</script>

<style scoped>
.cart-page {
  max-width: 1200px;
  margin: 28px auto;
  padding: 0 16px;
  box-sizing: border-box;
}
.cart-header {
  margin-bottom: 18px;
}
.cart-header h1 {
  margin: 0 0 6px 0;
  color: #1e90ff;
}
.subtitle { color: #666; margin: 0 0 12px 0; }

.cart-main { display: block; }
.loading { padding: 24px; text-align: center; color: #666; }

.cart-content {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 20px;
  align-items: start;
}

/* Cart list */
.cart-list { background: #fff; padding: 12px; border-radius: 10px; box-shadow: 0 6px 18px rgba(0,0,0,0.04); }
.cart-row {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 8px;
  border-bottom: 1px solid #f1f1f1;
  align-items: center;
}
.cart-row:last-child { border-bottom: none; }

.row-left { display:flex; gap:12px; align-items:center; }
.thumb { width: 84px; height: 84px; object-fit:cover; border-radius:8px; background:#f7f7f7; }
.meta .name { font-weight:700; color:#333; }
.meta-sub { color:#888; font-size:13px; margin-top:6px; display:flex; gap:10px; align-items:center; }

.row-right { display:flex; gap:12px; align-items:center; min-width:260px; justify-content:flex-end; }
.qty-controls { display:flex; align-items:center; gap:8px; }
.qty-btn { width:34px; height:34px; border-radius:6px; border:1px solid #ddd; background:#fff; cursor:pointer; font-size:18px; }
.qty-input { width:56px; text-align:center; padding:6px; border-radius:6px; border:1px solid #ddd; }

.line-total { font-weight:700; color:#1e90ff; min-width:120px; text-align:right; }
.row-actions .btn-link { background:transparent; border:none; color:#999; cursor:pointer; text-decoration:underline; }

/* Summary */
.cart-summary {
  background:#fff; padding:18px; border-radius:10px; box-shadow:0 6px 18px rgba(0,0,0,0.04);
}
.cart-summary h2 { margin:0 0 12px 0; color:#333; }
.summary-row { display:flex; justify-content:space-between; padding:8px 0; color:#444; }
.summary-row.total { font-size:18px; font-weight:800; margin-top:8px; border-top:1px dashed #eee; padding-top:12px; }
.summary-price { color:#1e90ff; font-weight:700; }

.summary-actions { margin-top:14px; display:flex; flex-direction:column; gap:10px; }
.btn-primary {
  display:inline-block; text-align:center; padding:12px 14px; background:#1e90ff; color:#fff; border-radius:8px; text-decoration:none; border:none; cursor:pointer;
}
.btn-primary.full { width:100%; }
.btn-secondary { display:inline-block; text-align:center; padding:10px 12px; background:#fff; color:#333; border-radius:8px; border:1px solid #ddd; text-decoration:none; }

.note { margin-top:12px; color:#777; font-size:13px; }

/* Empty state */
.empty { text-align:center; padding:36px; }
.empty .btn-primary { margin-top:12px; }

/* QR modal */
.qr-modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1200;
}
.qr-card {
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  width: 360px;
  text-align: center;
  box-shadow: 0 12px 40px rgba(0,0,0,0.2);
}
.qr-card h3 { margin: 0 0 8px 0; color: #333; }
.qr-sub { margin: 6px 0 12px 0; color: #666; font-size: 14px; }
.qr-img { width: 260px; height: 260px; object-fit: contain; margin: 8px auto; background: #fff; padding: 8px; border-radius: 8px; }
.qr-help { color: #666; font-size: 13px; margin-top: 8px; }
.qr-actions { display:flex; gap:10px; justify-content:center; margin-top:12px; }
.qr-actions .btn-primary { padding: 10px 12px; background:#1e90ff; }
.qr-actions .btn-secondary { padding: 10px 12px; }

/* Responsive */
@media (max-width: 980px) {
  .cart-content { grid-template-columns: 1fr; }
  .row-right { min-width: auto; justify-content:flex-start; flex-direction:column; align-items:flex-end; }
  .qr-card { width: 92%; }
  .qr-img { width: 220px; height: 220px; }
}
</style>

