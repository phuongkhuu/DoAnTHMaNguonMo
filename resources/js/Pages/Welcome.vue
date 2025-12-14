<template>
  <div>
    <!-- Header -->
    <header class="thanh-bar">
      <div class="logo"><a href="/">FASHION STYLE STORE</a></div>

      <div class="thanh-tim-kiem">
        <input v-model="searchQuery" @keyup.enter="search" type="text" placeholder="Tìm kiếm hoa theo tên..." />
        <button class="btn-tim-kiem" @click="search">Tìm kiếm</button>
      </div>

      <div class="hanh-dong-nguoi-dung">
        <template v-if="!user">
          <a href="/login" class="dang-nhap" data-inertia="false">Đăng nhập</a>
          <a href="/register" class="dang-ky" data-inertia="false">Đăng ký</a>
        </template>

        <template v-else>
          <div class="dropdown">
            <button class="nut-dropdown">Xin chào, <strong>{{ user.name }}</strong></button>
            <div class="noi-dung-dropdown">
              <a :href="route('profile.edit')" class="ho-so" v-if="hasRoute('profile.edit')">Hồ sơ</a>
              <a href="#" class="dang-xuat" @click.prevent="logout">Đăng xuất</a>
            </div>
          </div>
        </template>

        <a href="/giohang" class="gio-hang" @click.prevent="onCartIconClick">
            Giỏ hàng <span class="so-luong-gio-hang">{{ totalQuantity }}</span>
          </a>


        <div v-show="cartVisible" class="cart-dropdown">
          <p v-if="cartData.length === 0" class="cart-empty">Bạn chưa thêm sản phẩm nào.</p>
          <div id="cart-items">
            <div v-for="(item, index) in cartData" :key="item.id || index" class="cart-item">
              <div class="cart-item-content">
                <span class="product-name">{{ item.name }}</span>
                <button class="remove-item" @click="removeFromCart(item.id, index)">X</button>
              </div>
              <div class="quantity-controls">
                <button @click="updateCartItem(item.id, Math.max(0, item.quantity - 1))">-</button>
                <span class="quantity">x{{ item.quantity }}</span>
                <button @click="updateCartItem(item.id, item.quantity + 1)">+</button>
              </div>
              <span class="price">{{ formatPrice((item.price || 0) * (item.quantity || 0)) }}</span>
            </div>
          </div>
          <p class="cart-total">Tổng: <strong>{{ formatPrice(cartTotal) }}</strong></p>
          <div class="cart-actions">
            <a href="/giohang" class="btn-cart" data-inertia="false">Xem giỏ hàng</a>
            <a href="/checkout" class="btn-checkout" data-inertia="false">Thanh toán</a>
          </div>
        </div>
      </div>
    </header>

    <!-- Menu -->
    <nav class="menu-chinh">
      <div class="dropdown">
        <button class="nut-dropdown">☰ Danh mục sản phẩm ▾</button>
        <div class="noi-dung-dropdown">
          <a
            v-for="cat in categories"
            :key="cat.id"
            href="#"
            @click.prevent="openCategory(cat.slug)"
          >
            {{ cat.name }}
          </a>
        </div>
      </div>

      <a href="#" @click.prevent="setView('home', '')">TRANG CHỦ</a>
      <a href="#" @click.prevent="setView('shop', '')">CỬA HÀNG</a>
      <a href="#" @click.prevent="setView('contact', '')">LIÊN HỆ</a>
      <a href="#" @click.prevent="setView('about', '')">VỀ CHÚNG TÔI</a>
    </nav>

    <!-- Slideshow -->
    <div v-if="currentView === 'home'" class="slideshow-container">
      <div v-for="(slide, index) in slides" :key="index" class="mySlides fade" v-show="currentSlideIndex === index">
        <img :src="slide" class="banner-img" />
      </div>
      <a class="prev" @click="prevSlide">&#10094;</a>
      <a class="next" @click="nextSlide">&#10095;</a>
    </div>

    <!-- Home content -->
    <main>
      <!-- Best Sellers (home) -->
      <section v-if="currentView === 'home'" class="best-sellers">
        <h2>Sản phẩm bán chạy</h2>
        <div class="list">
          <div
            v-for="product in bestSellers"
            :key="product.id"
            class="product-card"
            @click.prevent="openProduct(product.slug, product)"
            role="button"
            tabindex="0"
          >
            <div class="image-container">
              <img :src="product.image || placeholderImage" :alt="product.name" />
              <button class="add-to-cart" @click.stop="addToCart(product)">Thêm Vào Giỏ</button>
            </div>
            <h3>{{ product.name }}</h3>
            <p v-if="product.short_description">{{ product.short_description }}</p>
            <div class="price">
              <span v-if="product.original_price" class="original-price">{{ formatPrice(product.original_price) }}</span>
              <span class="discounted-price">{{ formatPrice(product.price) }}</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Category view (products grid + back link below grid) -->
      <section v-if="currentView === 'category'" class="product-list">
        <h2 class="category-title">Danh mục: {{ categoryName }}</h2>

        <!-- product grid -->
        <div class="list category-grid">
          <div
            v-for="p in categoryProducts"
            :key="p.id"
            class="product-card"
            @click.prevent="openProduct(p.slug, p)"
            role="button"
            tabindex="0"
          >
            <div class="image-container">
              <img :src="p.image || placeholderImage" :alt="p.name" />
              <button class="add-to-cart" @click.stop="addToCart(p)">Thêm Vào Giỏ</button>
            </div>
            <h3>{{ p.name }}</h3>
            <p v-if="p.short_description">{{ p.short_description }}</p>
            <div class="price">{{ formatPrice(p.price) }}</div>
          </div>
        </div>

        <!-- full-width back link placed after the grid -->
        <div class="back-home">
          <a href="#" @click.prevent="setView('home', '')" class="btn-back">← Quay về trang chủ</a>
        </div>
      </section>

      <!-- Product detail view -->
      <section v-if="currentView === 'product'" class="product-detail">
        <div class="detail-card">
          <div class="detail-media">
            <img :src="currentProduct?.image || placeholderImage" :alt="currentProduct?.name || 'Sản phẩm'" />
          </div>

          <div class="detail-info">
            <h1 class="detail-title">{{ currentProduct?.name || 'Sản phẩm không tồn tại' }}</h1>

            <p class="detail-price" v-if="currentProduct">
              <span v-if="currentProduct.original_price" class="original-price">{{ formatPrice(currentProduct.original_price) }}</span>
              <span class="discounted-price">{{ formatPrice(currentProduct.price) }}</span>
            </p>

            <p class="detail-desc" v-if="currentProduct && currentProduct.description">
              {{ currentProduct.description }}
            </p>

            <p v-else class="detail-desc">Mô tả sản phẩm đang được cập nhật.</p>

            <div class="detail-actions">
              <button class="add-to-cart-large" @click="addToCart(currentProduct)">Thêm vào giỏ</button>
              <a href="#" class="btn-back" @click.prevent="goBackFromProduct">← Quay lại</a>
            </div>
          </div>
        </div>
        <!-- 🔽 Reviews at the bottom -->
        <div class="reviews">
          <h2>Đánh giá sản phẩm</h2>
          <!-- Average rating -->
          <p v-if="productReviews.length">
            Trung bình: <span class="stars">{{ renderStars(averageRating) }}</span>
            ({{ productReviews.length }} lượt)
          </p>
          <p v-else><span style="font-style: italic ;">Chưa có đánh giá nào</span></p>
          <!-- Review list -->
          <ul class="review-list">
            <li v-for="r in productReviews" :key="r.id" class="review-item">
              <strong>{{ r.user.name }}</strong> —
              <span class="stars">{{ renderStars(r.rating) }}</span>
              <p>{{ r.comment }}</p>
            </li>
          </ul>

          <!-- Review form -->
          <div v-if="user" class="review-form">
            <h3>Viết đánh giá của bạn</h3>
            <form @submit.prevent="submitReview">
                <label>Điểm đánh giá:</label>
                <div class="star-selector">
                  <span
                    v-for="star in 5"
                    :key="star"
                    class="star"
                    :class="{ active: star <= newReview.rating }"
                    @click="newReview.rating = star"
                  >
                    ★
                  </span>
                </div>

                <label>Bình luận:</label>
                <textarea v-model="newReview.comment"></textarea>
                <button type="submit">Gửi</button>
              </form>
          </div>
        </div>
      </section>


      <!-- Shop view (inline list) -->
      <section v-if="currentView === 'shop'" class="product-list">
        <h2 class="category-title">Cửa hàng</h2>
        <div class="list">
          <div
            v-for="p in allProducts"
            :key="p.id"
            class="product-card"
            @click.prevent="openProduct(p.slug, p)"
            role="button"
            tabindex="0"
          >
            <div class="image-container">
              <img :src="p.image || placeholderImage" :alt="p.name" />
              <button class="add-to-cart" @click.stop="addToCart(p)">Thêm Vào Giỏ</button>
            </div>
            <h3>{{ p.name }}</h3>
            <p v-if="p.short_description">{{ p.short_description }}</p>
            <div class="price">{{ formatPrice(p.price) }}</div>
          </div>
        </div>

        <!-- pagination controls for shop (if using paginator) -->
        <div v-if="productsPaginator.meta" class="pagination">
          <button
            class="page-btn"
            :disabled="!productsPaginator.prev_page_url"
            @click="goToProductPage(productsPaginator.meta.current_page - 1)"
          >
            Trước
          </button>

          <span class="page-info">Trang {{ productsPaginator.meta.current_page }} / {{ productsPaginator.meta.last_page }}</span>

          <button
            class="page-btn"
            :disabled="!productsPaginator.next_page_url"
            @click="goToProductPage(productsPaginator.meta.current_page + 1)"
          >
            Sau
          </button>
        </div>
      </section>
      <!-- Contact view -->
      <section v-if="currentView === 'contact'" class="contact">
        <h2>Liên hệ chúng tôi</h2>
        <form class="contact-form">
          <label for="name">Họ và tên:</label>
          <input type="text" id="name" placeholder="Nhập họ tên của bạn" required>

          <label for="email">Email:</label>
          <input type="email" id="email" placeholder="Nhập email của bạn" required>

          <label for="phone">Số điện thoại:</label>
          <input type="text" id="phone" placeholder="+84" required>

          <label for="message">Nội dung:</label>
          <textarea id="message" placeholder="Nhập nội dung liên hệ" required></textarea>

          <button type="submit">Gửi liên hệ</button>
        </form>
      </section>
      <!-- About view -->
      <section v-if="currentView === 'about'" class="about">
          <div class="about-container">
            <div class="about-image">
              <img src="/image/about.png" alt="Giới thiệu về chúng tôi">
              <img src="/image/vechungtoi.png" alt="Giới thiệu về chúng tôi">
            </div>
            <div class="about-text">
              <h2>FASHION STYLE STORE</h2>
              <p>
                Fashion Style Store là cửa hàng chuyên cung cấp quần áo thời trang chất lượng, mang đến những sản phẩm hiện đại, phong cách và phù hợp với xu hướng. Chúng tôi không chỉ bán quần áo, mà còn giúp bạn thể hiện cá tính và phong cách riêng của mình.
              </p>

              <h3>Sứ mệnh của chúng tôi</h3>
              <p>
                Chúng tôi cam kết mang đến những sản phẩm thời trang chất lượng cao với mức giá hợp lý, đáp ứng mọi nhu cầu từ trang phục hằng ngày, đi làm, đi chơi cho đến các sự kiện đặc biệt. Thời trang không chỉ là trang phục, mà còn là cách bạn thể hiện bản thân.
              </p>

              <h3>Những giá trị cốt lõi</h3>
              <ul>
                <li><strong>Chất lượng hàng đầu:</strong> Fashion Style Store luôn lựa chọn kỹ lưỡng từng sản phẩm, đảm bảo chất liệu tốt, bền đẹp và thoải mái.</li>
                <li><strong>Dịch vụ tận tâm:</strong> Đội ngũ nhân viên nhiệt tình, chuyên nghiệp luôn sẵn sàng tư vấn để bạn tìm được trang phục phù hợp nhất.</li>
                <li><strong>Sáng tạo và xu hướng:</strong> Chúng tôi cập nhật liên tục các mẫu thiết kế mới, bắt kịp xu hướng thời trang trong và ngoài nước.</li>
                <li><strong>Giao hàng nhanh chóng:</strong> Dịch vụ giao hàng tận nơi, đảm bảo sản phẩm đến tay khách hàng nhanh và đúng mô tả.</li>
              </ul>

              <h3>Cam kết của chúng tôi</h3>
              <p>
                Chúng tôi luôn nỗ lực cải thiện chất lượng sản phẩm và dịch vụ, lắng nghe mọi phản hồi để mang đến trải nghiệm mua sắm tốt nhất. Tại Fashion Style Store, mỗi sản phẩm đều được chọn lựa với sự tỉ mỉ và tâm huyết.
              </p>

              <p>
                Hãy để Fashion Style Store giúp bạn tự tin hơn mỗi ngày với phong cách thời trang ấn tượng và độc đáo!
              </p>
            </div>
          </div>
        </section>
    </main>
    <!-- Footer -->
    <footer class="footer">
      <div class="footer-content">
        <div class="store-info">
          <h1 style="color: white; font-size: 30px;"><strong>FASHION STYLE STORE</strong></h1>
          <p><strong>Địa chỉ:</strong> 180 Cao Lỗ, Phường 4, Quận 8, TP.HCM</p>
          <p><strong>Email:</strong> dh52201275@student.stu.edu.vn</p>
          <p><strong>Điện thoại:</strong> (028) 38 505 520</p>
        </div>
        <div class="map">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.9544102884747!2d106.67783209999999!3d10.737997199999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f62a90e5dbd%3A0x674d5126513db295!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBDw7RuZyBuZ2jhu4cgU8OgaSBHw7Ju!5e0!3m2!1svi!2s!4v1746779135460!5m2!1svi!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
      </div>
    </footer>

    <!-- Notification -->
    <div v-show="notification.visible" class="notification">{{ notification.message }}</div>
  </div>
</template>

<script>
import axios from 'axios';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

export default {
  name: "Welcome",
  data() {
    return {
      slides: [],
      currentSlideIndex: 0,

      notification: {
          visible: false,
          message: "",
          type: "info"
        },
      // UI / cart
      searchQuery: "",
      cartData: [], // server-driven for authenticated users; local fallback used only for guests
      cartVisible: false,
      notification: { visible: false, message: "" },

      // dynamic data
      bestSellers: [],
      allProducts: [],
      categories: [],
      blogs: [],
      categoryProducts: [],

      // paginator wrapper for shop products
      productsPaginator: {
        data: [],
        meta: null,
        prev_page_url: null,
        next_page_url: null
      },

      newsletterEmail: "",

      // SPA view state
      currentView: "home",
      currentSlug: "",

      // product detail
      currentProduct: null,
      placeholderImage: '/image/placeholder.png',

      // 🔥 product reviews
      productReviews: [],        
      newReview: {               
        rating: 5,
        comment: ""
      },
      averageRating: 0           
    };
  },
  computed: {
    cartTotal() {
      return this.cartData.reduce(
        (sum, item) => sum + (item.price || 0) * (item.quantity || 0),
        0
      );
    },
    totalQuantity() {
      if (!this.user) return 0;
      return this.cartData.reduce((sum, item) => sum + (item.quantity || 0), 0);
    },
    currentPost() {
      return this.blogs.find(b => b.slug === this.currentSlug) || null;
    },
    user() {
      return (this.$page &&
        this.$page.props &&
        this.$page.props.auth &&
        this.$page.props.auth.user)
        ? this.$page.props.auth.user
        : null;
    },
    categoryName() {
      if (!this.currentSlug) return '';
      const cat = (this.categories || []).find(c => c.slug === this.currentSlug);
      return cat ? cat.name : this.currentSlug;
    },

    // 🔥 Reviews
    averageRating() {
      if (!this.productReviews || !this.productReviews.length) return 0;
      const sum = this.productReviews.reduce((acc, r) => acc + (r.rating || 0), 0);
      return (sum / this.productReviews.length).toFixed(1);
    },
    reviewCount() {
      return this.productReviews ? this.productReviews.length : 0;
    },
    currentReviews() {
      // if you want to filter reviews by current product slug
      if (!this.currentProduct) return [];
      return this.productReviews.filter(r => r.product_id === this.currentProduct.id);
    }
  },
  methods: {
    renderStars(value) {
      const full = Math.floor(value)
      const half = value - full >= 0.5 ? 1 : 0
      const empty = 5 - full - half
      return '★'.repeat(full) + (half ? '½' : '') + '☆'.repeat(empty)
    },
    async fetchReviews(slug) {
      try {
        const res = await axios.get(`/products/${slug}/reviews`);
        this.productReviews = res.data;
        // update average rating
        if (this.productReviews.length) {
          const sum = this.productReviews.reduce((acc, r) => acc + (r.rating || 0), 0);
          this.averageRating = (sum / this.productReviews.length).toFixed(1);
        } else {
          this.averageRating = 0;
        }
      } catch (err) {
        console.error("Failed to fetch reviews", err);
      }
    },

    async submitReview() {
      if (!this.user) {
        alert("Bạn cần đăng nhập để viết đánh giá.");
        return;
      }
      try {
        await axios.post(`/products/${this.currentProduct.slug}/reviews`, {
            product_id: this.currentProduct.id, 
            rating: this.newReview.rating,
            comment: this.newReview.comment
          });
        await this.fetchReviews(this.currentProduct.slug);
        this.newReview = { rating: 5, comment: "" };
        this.showNotice("Đánh giá đã được gửi!", "success");
      } catch (err) {
        console.error("Failed to submit review", err);
        this.showNotice("Gửi đánh giá thất bại", "error");
      }
    },
    async updateReview(reviewId, rating, comment) {
      try {
        await axios.put(`/products/reviews/${reviewId}`, { rating, comment });
        await this.fetchReviews(this.currentProduct.slug);
        this.showNotice("Đánh giá đã được cập nhật", "success");
      } catch (err) {
        console.error("Failed to update review", err);
        this.showNotice("Cập nhật đánh giá thất bại", "error");
      }
    },
    // Optional: delete a review (only if you add delete UI)
    async deleteReview(reviewId) {
      try {
        await axios.delete(`/products/reviews/${reviewId}`);
        await this.fetchReviews(this.currentProduct.slug);
        this.showNotice("Đánh giá đã được xóa", "success");
      } catch (err) {
        console.error("Failed to delete review", err);
        this.showNotice("Xóa đánh giá thất bại", "error");
      }
    },
    formatPrice(value) {
      if (typeof value !== 'number') return value;
      return value.toLocaleString("vi-VN") + "₫";
    },
    showNotification(message, type = "info") {
    // Simple example: set a reactive notification object
    this.notification = {
      visible: true,
      message,
      type
    };

    // Auto-hide after 3 seconds
    setTimeout(() => {
      this.notification.visible = false;
    }, 3000);
  },

    // --- Cart: per-user server-backed behavior ---
    async addToCart(product) {
      if (!product) return;

      // If user not logged in, redirect to login
      if (!this.user) {
        window.location.href = '/login';
        return;
      }

      try {
        const payload = {
          product_id: product.id ?? null,
          name: product.name,
          price: product.price ?? 0,
          image: product.image ?? '',
          quantity: 1
        };
        await axios.post('/user/cart', payload);
        await this.fetchUserCart();
        this.showNotification(`✔ Đã thêm ${product.name} vào giỏ hàng!`);
      } catch (err) {
        console.error('addToCart error', err);
        if (err.response?.status === 401) {
          window.location.href = '/login';
          return;
        }
        this.showNotification('Lỗi khi thêm vào giỏ hàng');
      }
    },
    async onCartIconClick() {
      // If not logged in, go to login
      if (!this.user) {
        window.location.href = '/login';
        return;
      }

      // If using Inertia, use it to navigate (preserves SPA behavior)
      if (this.$inertia && typeof this.$inertia.visit === 'function') {
        this.$inertia.visit('/giohang');
        return;
      }

      // Fallback: full page navigation
      window.location.href = '/giohang';
    },
    async fetchUserCart() {
      try {
        const res = await axios.get('/user/cart');
        // Expect server returns array of items
        this.cartData = Array.isArray(res.data) ? res.data : (res.data.items ?? []);
      } catch (err) {
        console.error('fetchUserCart error', err);
        if (err.response?.status === 401) {
          window.location.href = '/login';
          return;
        }
        this.cartData = [];
        this.showNotification('Không thể tải giỏ hàng');
      }
    },

    async removeFromCart(itemId, index) {
      if (!this.user) {
        window.location.href = '/login';
        return;
      }
      if (!itemId) {
        // fallback local removal
        this.cartData.splice(index, 1);
        return;
      }
      try {
        await axios.delete(`/user/cart/${itemId}`);
        await this.fetchUserCart();
        this.showNotification('Đã xóa sản phẩm khỏi giỏ hàng');
      } catch (err) {
        console.error('removeFromCart error', err);
        this.showNotification('Xóa thất bại');
      }
    },

    async updateCartItem(itemId, newQty) {
      if (!this.user) {
        window.location.href = '/login';
        return;
      }
      if (!itemId) return;
      try {
        if (newQty <= 0) {
          await axios.delete(`/user/cart/${itemId}`);
        } else {
          await axios.put(`/user/cart/${itemId}`, { quantity: newQty });
        }
        await this.fetchUserCart();
      } catch (err) {
        console.error('updateCartItem error', err);
        this.showNotification('Cập nhật giỏ hàng thất bại');
      }
    },
    async fetchBanners() {
      try {
        const res = await axios.get('/banners');
      this.slides = res.data
      .filter(b => b.active)
      .map(b => b.image);
      } catch (err) {
        console.error('Failed to load banners', err);
      }
    },

    // --- Existing local cart helpers (kept for guests, but server is primary for logged-in users) ---
    saveCart() {
      // keep local fallback for guests
      localStorage.setItem("cartData", JSON.stringify(this.cartData));
    },

    // --- Other helpers (search, navigation, product open) ---
    prevSlide() {
      this.currentSlideIndex = (this.currentSlideIndex - 1 + this.slides.length) % this.slides.length;
    },
    nextSlide() {
      this.currentSlideIndex = (this.currentSlideIndex + 1) % this.slides.length;
    },

    search() {
      this.setView("shop", "");
      const q = this.searchQuery.trim();
      if (q) history.replaceState({ view: "shop", q }, "", `/shop?q=${encodeURIComponent(q)}`);
      this.fetchAllProducts(q);
    },

    subscribeNewsletter() {
      if (this.newsletterEmail) {
        this.showNotification(`Đăng ký thành công: ${this.newsletterEmail}`);
        this.newsletterEmail = "";
      }
    },

    setView(view, slug = "") {
      this.currentView = view || "home";
      this.currentSlug = slug || "";
      let url = "/";
      if (view === "category" && slug) url = `/category/${slug}`;
      else if (view === "post" && slug) url = `/blog/${slug}`;
      else if (view === "shop") url = `/shop`;
      else if (view === "contact") url = `/contact`;
      else if (view === "about") url = `/about`;
      else if (view === "labs") url = `/labs`;
      history.pushState({ view: this.currentView, slug: this.currentSlug }, "", url);
      document.title = this.computeTitle();
      window.scrollTo({ top: 0, behavior: "smooth" });

      if (this.currentView === 'category' && this.currentSlug) {
        this.fetchProductsByCategorySlug(this.currentSlug);
      } else if (this.currentView === 'shop') {
        this.fetchAllProducts();
      }
    },

    openCategory(slug) {
      this.setView("category", slug);
    },

    openPost(slug) {
      this.setView("post", slug);
    },

    openProduct(slug, product = null) {
      if (product) {
        this.currentProduct = product;
      } else {
        const found =
          (this.allProducts || []).find(p => p.slug === slug) ||
          (this.categoryProducts || []).find(p => p.slug === slug) ||
          null;
        this.currentProduct = found;
      }

      this.currentView = 'product';
      this.currentSlug = slug;

      const url = `/product/${slug}`;
      history.pushState({ view: 'product', slug }, '', url);
      document.title = this.currentProduct?.name
        ? `${this.currentProduct.name} - FASHION STYLE STORE`
        : 'Sản phẩm - FASHION STYLE STORE';
      window.scrollTo({ top: 0, behavior: 'smooth' });

      this.fetchReviews(slug);
    },

    goBackFromProduct() {
      if (history.state && history.state.view) {
        history.back();
        return;
      }
      this.setView('shop', '');
    },

    openFakeUrl(url) {
      window.open(url, '_blank');
    },

    computeTitle() {
      if (this.currentView === "category") return `Danh mục: ${this.categoryName} - FASHION STYLE STORE`;
      if (this.currentView === "post") {
        const p = this.currentPost;
        return p ? `${p.title} - FASHION STYLE STORE` : "Tin tức - FASHION STYLE STORE";
      }
      if (this.currentView === "shop") return "Cửa hàng - FASHION STYLE STORE";
      if (this.currentView === "contact") return "Liên hệ - FASHION STYLE STORE";
      if (this.currentView === "about") return "Về chúng tôi - FASHION STYLE STORE";
      if (this.currentView === "labs") return "Lab thực hành - FASHION STYLE STORE";
      if (this.currentView === "product") return this.currentProduct?.name ? `${this.currentProduct.name} - FASHION STYLE STORE` : 'Sản phẩm - FASHION STYLE STORE';
      return "FASHION STYLE STORE";
    },

    async applyPath(pathname, replaceState = false) {
      const path = pathname.replace(/^\/+|\/+$/g, "");
      if (!path) {
        this.currentView = "home";
        this.currentSlug = "";
        if (replaceState) history.replaceState({ view: "home" }, "", "/");
        return;
      }
      const parts = path.split("/");
      if (parts[0] === "category" && parts[1]) {
        this.currentView = "category";
        this.currentSlug = parts[1];
        if (replaceState) history.replaceState({ view: "category", slug: parts[1] }, "", pathname);
        this.fetchProductsByCategorySlug(parts[1]);
        return;
      }
      if (parts[0] === "blog" && parts[1]) {
        this.currentView = "post";
        this.currentSlug = parts[1];
        if (replaceState) history.replaceState({ view: "post", slug: parts[1] }, "", pathname);
        return;
      }
      if (parts[0] === "shop") {
        this.currentView = "shop";
        this.currentSlug = "";
        if (replaceState) history.replaceState({ view: "shop" }, "", pathname);
        this.fetchAllProducts();
        return;
      }
      if (parts[0] === "product" && parts[1]) {
        const slug = parts[1];
        this.currentView = 'product';
        this.currentSlug = slug;
        if (replaceState) history.replaceState({ view: 'product', slug }, '', pathname);
        const found =
          (this.allProducts || []).find(p => p.slug === slug) ||
          (this.categoryProducts || []).find(p => p.slug === slug) ||
          null;
        if (found) {
          this.currentProduct = found;
          this.fetchReviews(slug);
        } else {
          try {
            const res = await axios.get(`/products/${slug}`);
            this.currentProduct = res.data;
            this.fetchReviews(slug); 
          } catch {
            this.currentProduct = null;
          }
        }
        return;
      }
      if (parts[0] === "contact") {
        this.currentView = "contact";
        if (replaceState) history.replaceState({ view: "contact" }, "", pathname);
        return;
      }
      if (parts[0] === "about") {
        this.currentView = "about";
        if (replaceState) history.replaceState({ view: "about" }, "", pathname);
        return;
      }
      this.currentView = "home";
      this.currentSlug = "";
      if (replaceState) history.replaceState({ view: "home" }, "", "/");
    },

    logout() {
      if (this.$inertia && typeof this.$inertia.post === "function") {
        this.$inertia.post(route("logout"));
      } else {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "/logout";
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
        if (token) {
          const input = document.createElement("input");
          input.type = "hidden";
          input.name = "_token";
          input.value = token;
          form.appendChild(input);
        }
        document.body.appendChild(form);
        form.submit();
      }
    },

    hasRoute(name) {
      try {
        return typeof route === "function" && !!route(name);
      } catch (e) {
        return false;
      }
    },

    // ----- Data fetching helpers -----
    normalizePaginator(payload) {
      if (!payload) return { data: [], meta: null, prev_page_url: null, next_page_url: null };
      if (Array.isArray(payload)) return { data: payload, meta: null, prev_page_url: null, next_page_url: null };
      return {
        data: payload.data ?? [],
        meta: payload ? {
          total: payload.total ?? null,
          current_page: payload.current_page ?? null,
          last_page: payload.last_page ?? null,
          per_page: payload.per_page ?? null,
        } : null,
        prev_page_url: payload.prev_page_url ?? null,
        next_page_url: payload.next_page_url ?? null
      };
    },

    async fetchCategories() {
      try {
        const res = await axios.get('/categories');
        if (typeof res.data === 'string' && res.data.trim().startsWith('<')) {
          console.warn('Server returned HTML for /categories');
          this.categories = [];
          return;
        }
        this.categories = res.data || [];
      } catch (err) {
        console.error('Không thể tải danh mục', err);
        this.categories = [];
      }
    },

    async fetchHomepageData() {
      try {
        const prodRes = await axios.get('/products', { params: { per_page: 12 } });
        if (typeof prodRes.data === 'string' && prodRes.data.trim().startsWith('<')) {
          console.warn('Server returned HTML for /products');
          this.allProducts = [];
          this.bestSellers = [];
        } else {
          const pag = this.normalizePaginator(prodRes.data);
          this.allProducts = pag.data;
          const bestRes = await axios.get('/products', { params: { best_seller: true } });
          const bestPag = this.normalizePaginator(bestRes.data);
          this.bestSellers = bestPag.data.slice(0, 6);
        }
      } catch (err) {
        console.warn('Không thể tải sản phẩm cho trang chủ', err);
        this.allProducts = [];
        this.bestSellers = [];
      }

      try {
        const postRes = await axios.get('/posts', { params: { per_page: 6 } });
        if (typeof postRes.data === 'string' && postRes.data.trim().startsWith('<')) {
          console.warn('Server returned HTML for /posts');
          this.blogs = [];
        } else {
          const postsPag = this.normalizePaginator(postRes.data);
          this.blogs = postsPag.data;
        }
      } catch (err) {
        console.warn('Không thể tải tin tức', err);
        this.blogs = [];
      }
    },

    async fetchAllProducts(q = "", page = 1) {
      try {
        const params = { per_page: 12, page };
        if (q) params.q = q;
        const res = await axios.get('/products', { params });
        if (typeof res.data === 'string' && res.data.trim().startsWith('<')) {
          console.warn('Server returned HTML for /products');
          this.allProducts = [];
          this.productsPaginator = { data: [], meta: null, prev_page_url: null, next_page_url: null };
          return;
        }
        const pag = this.normalizePaginator(res.data);
        this.productsPaginator = pag;
        this.allProducts = pag.data;
      } catch (err) {
        console.error('Không thể tải sản phẩm', err);
        this.allProducts = [];
        this.productsPaginator = { data: [], meta: null, prev_page_url: null, next_page_url: null };
      }
    },

    async goToProductPage(page) {
      if (!this.productsPaginator.meta) return;
      const target = Math.max(1, Math.min(page, this.productsPaginator.meta.last_page || page));
      await this.fetchAllProducts(this.searchQuery.trim(), target);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    async fetchProductsByCategorySlug(slug) {
      try {
        const catRes = await axios.get('/categories');
        if (typeof catRes.data === 'string' && catRes.data.trim().startsWith('<')) {
          console.warn('Server returned HTML for /categories');
          this.categoryProducts = [];
          return;
        }
        const cat = (catRes.data || []).find(c => c.slug === slug);
        if (!cat) {
          this.categoryProducts = [];
          return;
        }
        const res = await axios.get('/products', { params: { category: cat.id, per_page: 24 } });
        if (typeof res.data === 'string' && res.data.trim().startsWith('<')) {
          console.warn('Server returned HTML for /products by category');
          this.categoryProducts = [];
          return;
        }
        const pag = this.normalizePaginator(res.data);
        this.categoryProducts = pag.data;
      } catch (err) {
        console.error('Không thể tải sản phẩm theo danh mục', err);
        this.categoryProducts = [];
      }
    }
  },

  mounted() {
    this.fetchBanners();

    // slideshow auto-advance
    setInterval(this.nextSlide, 5000);

    // initialize view from current URL
    this.applyPath(location.pathname, true);
    document.title = this.computeTitle();

    // initial data (public endpoints)
    this.fetchCategories();
    this.fetchHomepageData();

    // if user logged in, fetch server cart
    if (this.user) {
      this.fetchUserCart();
    } else {
      // keep local fallback for guests
      try {
        const local = JSON.parse(localStorage.getItem("cartData") || "[]");
        this.cartData = Array.isArray(local) ? local : [];
      } catch {
        this.cartData = [];
      }
    }

    // handle back/forward
    window.addEventListener("popstate", (event) => {
      const st = event.state;
      if (st && st.view) {
        this.currentView = st.view;
        this.currentSlug = st.slug || "";
      } else {
        this.applyPath(location.pathname, true);
      }
      document.title = this.computeTitle();

      if (this.currentView === 'category' && this.currentSlug) {
        this.fetchProductsByCategorySlug(this.currentSlug);
      } else if (this.currentView === 'shop') {
        this.fetchAllProducts();
      } else if (this.currentView === 'product' && this.currentSlug) {
        const found =
          (this.allProducts || []).find(p => p.slug === this.currentSlug) ||
          (this.categoryProducts || []).find(p => p.slug === this.currentSlug) ||
          null;
        this.currentProduct = found;
      }
    });
  }
};
</script>

<style scoped>
@import '../../css/style.css';

.reviews {
  width: 100%;
  max-width: none;
  margin: 40px 0 0 0;
  padding: 30px 60px;
  background-color: #fdfdfd;
  border-top: 1px solid #eee;
  border-radius: 8px;
  clear: both;
  box-sizing: border-box;
}


.reviews h2 {
  font-size: 22px;
  margin-bottom: 10px;
  color: #1e88ff;
}

.star-selector {
  display: flex;
  gap: 6px;
  font-size: 24px;
  margin: 8px 0;
  cursor: pointer;
}

.stars{
    color: gold;
}

.star {
  color: #ccc;
  transition: color 0.2s;
}

.star.active {
  color: gold;
}


.review-list {
  list-style: none;
  padding-left: 0;
  margin-top: 10px;
}

.review-item {
  margin-bottom: 16px;
  padding-bottom: 10px;
  border-bottom: 1px solid #ddd;
}

.review-item strong {
  font-weight: bold;
  color: #333;
}

.review-form {
  margin-top: 20px;
}

.review-form h3 {
  margin-bottom: 10px;
  font-size: 18px;
  color: #444;
}

.review-form label {
  display: block;
  margin-top: 10px;
  font-weight: 500;
}

.review-form input[type="number"],
.review-form textarea {
  width: 100%;
  padding: 8px;
  margin-top: 5px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.review-form button {
  margin-top: 12px;
  padding: 8px 16px;
  background-color: #1e88ff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.review-form button:hover {
  background-color: #1565d8;
}


/* small overrides for cart panel */
.cart-panel {
  background: white;
  padding: 16px;
  width: 320px;
  max-height: 80vh;
  overflow-y: auto;
  border-radius: 8px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}
.cart-dropdown {
  position: absolute;
  right: 20px;
  top: 60px;
  z-index: 999;
  background: #fff;
  padding: 12px;
  border-radius: 8px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.08);
  width: 320px;
  max-height: 70vh;
  overflow: auto;
}

/* Category heading: full width and centered */
.product-list .category-title {
  display: block;
  width: 100%;
  text-align: center;
  margin: 18px 0;
  font-size: 28px;
  font-weight: 700;
  color: #4da6ff;
  line-height: 1.2;
}

/* Equal-height product grid using CSS Grid */
.product-list .list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 18px;
  justify-items: center;
  align-items: start;
  width: 100%;
  box-sizing: border-box;
  margin-top: 12px;
  padding: 0;
}

/* Make each card a column flex container so children can stretch */
.product-list .product-card {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: stretch;
  width: 100%;
  max-width: 290px;
  min-height: 440px;
  padding: 14px;
  box-sizing: border-box;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

/* Image area: fixed height so all cards look consistent */
.product-card .image-container {
  width: 100%;
  height: 220px;
  overflow: hidden;
  border-radius: 10px;
  position: relative;
  background: #fff;
  margin-bottom: 12px;
}

/* Ensure image covers the container uniformly */
.product-card .image-container img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* Title: limit lines so long titles don't break layout */
.product-card h3 {
  font-size: 18px;
  color: #0d47a1;
  margin: 0;
  line-height: 1.2;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Short description: optional, clamp to 2 lines */
.product-card p {
  margin: 0;
  color: #555;
  font-size: 14px;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Price and actions area pinned to bottom */
.product-card .price {
  margin-top: 12px;
  font-weight: 700;
  color: #077bff;
}

/* Make add-to-cart button consistent and at bottom */
.product-card .add-to-cart {
  margin-top: 12px;
  align-self: center;
  padding: 10px 14px;
  background: #037df7;
  color: #fff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

/* Cart dropdown item styles */
.cart-item { display:flex; justify-content:space-between; align-items:center; gap:8px; padding:8px 0; border-bottom:1px solid #f1f1f1; }
.cart-item-content { display:flex; gap:8px; align-items:center; }
.remove-item { background:transparent; border:none; color:#999; cursor:pointer; }
.quantity-controls { display:flex; align-items:center; gap:8px; }
.quantity-controls button { padding:4px 8px; border-radius:6px; border:1px solid #ddd; background:#fff; cursor:pointer; }


.product-detail{
  flex-direction: column;
}
/* Product detail styles */
.product-detail .detail-card {
  display: flex;
  gap: 24px;
  max-width: 1100px;
  margin: 24px auto;
  padding: 18px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.06);
}
.detail-media img {
  width: 420px;
  height: 420px;
  object-fit: cover;
  border-radius: 10px;
}
.detail-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}
.detail-title {
  font-size: 28px;
  color: rgb(47, 36, 255);
  margin: 0 0 12px 0;
}
.detail-price { font-size: 22px; margin-bottom: 12px; color: #1e88ff; }
.detail-desc { color: #444; line-height: 1.5; margin-bottom: 18px; }
.detail-actions { display:flex; gap:12px; align-items:center; margin-bottom:12px; }
.add-to-cart-large {
  background: #1e88ff;
  color: #fff;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  cursor: pointer;
}
.btn-back { color: #666; text-decoration: underline; }
.fake-url { margin-top: 12px; color: #666; font-size: 14px; }
.fake-url a { color: #1e88ff; text-decoration: none; }
.fake-url a:hover { text-decoration: underline; }

/* Back link stays full width below the grid */
.product-list .back-home {
  width: 100%;
  margin-top: 24px;
  display: flex;
  justify-content: center;
  align-items: center;
  clear: both;
}

/* Style the link as a button */
.product-list .back-home .btn-back {
  display: inline-block;
  padding: 10px 16px;
  border-radius: 6px;
  background: #fff;
  border: 1px solid #ddd;
  color: #333;
  text-decoration: none;
  font-weight: 600;
  transition: background .12s, transform .06s;
}
.product-list .back-home .btn-back:hover {
  background: #f7f7f7;
  transform: translateY(-1px);
}

/* Pagination */
.pagination { display:flex; justify-content:center; align-items:center; gap:12px; margin:16px 0; width:100%; }
.page-btn { background: var(--blue-500, #1e88ff); color:#fff; border:1px solid var(--blue-500, #1e88ff); padding:8px 14px; border-radius:8px; font-weight:600; cursor:pointer; }
.page-btn:disabled { background:#cfe6ff; border-color:#cfe6ff; cursor:not-allowed; opacity:0.6; }
.page-info { color:#444; font-weight:600; min-width:140px; text-align:center; }

/* Notification */
.notification {
  display: block;
  position: fixed;
  bottom: 20px;
  right: 20px;
  background:#1f75ff;
  color: white;
  padding: 12px;
  border-radius: 5px;
  font-weight: bold;
  z-index: 1200;
}

/* Responsive */
@media (max-width: 768px) {
  .reviews {
    padding: 20px;
  }
}
@media (max-width: 900px) {
  .product-detail .detail-card { flex-direction: column; align-items: center; }
  .detail-media img { width: 100%; height: auto; max-height: 420px; }
  .detail-info { width: 100%; }
}
@media (max-width: 768px) {
  .product-list .list {
    grid-template-columns: 1fr;
  }
  .product-card {
    max-width: 720px;
    min-height: auto;
  }
}
</style>

