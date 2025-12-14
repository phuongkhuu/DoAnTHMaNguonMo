<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const loading = ref(false);
const notification = reactive({ visible: false, message: '', type: 'info' });

function showNotice(msg, type = 'info') {
  notification.message = msg;
  notification.type = type;
  notification.visible = true;
  setTimeout(() => (notification.visible = false), 3000);
}

const reviews = ref([]);
const reviewLoading = ref(false);
const reviewFormVisible = ref(false);
const editingReview = reactive({
  id: null,
  product_id: null,
  user_name: '',
  rating: 5,
  comment: '',
  active: true,
});

const activeTab = ref('products'); 

const banners = ref([]);          // list of banners
const bannerLoading = ref(false); // loading state for banners
const bannerFormVisible = ref(false);
const editingBanner = reactive({
  id: null,
  alt: '',
  sort_order: 0,
  active: true,
});
const bannerFile = ref(null);

const previewBanner = computed(() => {
  if (bannerFile.value) {
    return URL.createObjectURL(bannerFile.value);
  }
  return editingBanner.image || null;
});

const receipts = ref([])
const receiptLoading = ref(false)
const selectedReceipt = ref(null)
const receiptModalVisible = ref(false)

const products = ref({
  data: [],
  meta: { total: 0, current_page: 1, last_page: 1, per_page: 12 },
  prev_page_url: null,
  next_page_url: null,
});
const productPage = ref(1);
const productFormVisible = ref(false);
const editingProduct = reactive({
  id: null,
  name: '',
  slug: '',
  category_id: null,
  price: 0,
  original_price: null,
  short_description: '',
  description: '',
  image: '',
  is_best_seller: false,
});
const productImageFile = ref(null);

const previewUrl = ref(null);

watch(productImageFile, (file) => {
  if (previewUrl.value && typeof previewUrl.value === 'string' && previewUrl.value.startsWith('blob:')) {
    try { URL.revokeObjectURL(previewUrl.value); } catch (e) {}
  }
  if (file) {
    previewUrl.value = URL.createObjectURL(file);
  } else {
    previewUrl.value = editingProduct.image || null;
  }
});

watch(() => editingProduct.image, (val) => {
  if (!productImageFile.value) {
    if (previewUrl.value && typeof previewUrl.value === 'string' && previewUrl.value.startsWith('blob:')) {
      try { URL.revokeObjectURL(previewUrl.value); } catch (e) {}
    }
    previewUrl.value = val || null;
  }
});

const previewSrc = computed(() => previewUrl.value);
const categories = ref([]);
const categoryLoading = ref(false);
const categoryFormVisible = ref(false);
const editingCategory = reactive({
  id: null,
  name: '',
  slug: '',
  image: '',
  sort_order: 0,
});
const categoryImageFile = ref(null);

async function apiGet(url, params = {}) {
  return axios.get(url, { params });
}
async function apiPost(url, data, config = {}) {
  return axios.post(url, data, config);
}
async function apiPut(url, data) {
  return axios.put(url, data);
}
async function apiDelete(url) {
  return axios.delete(url);
}

function normalizePaginator(payload) {
  if (!payload) {
    return { data: [], meta: { total: 0, current_page: 1, last_page: 1, per_page: 12 }, prev_page_url: null, next_page_url: null };
  }
  if (Array.isArray(payload)) {
    return { data: payload, meta: { total: payload.length, current_page: 1, last_page: 1, per_page: payload.length }, prev_page_url: null, next_page_url: null };
  }
  return {
    data: payload.data ?? [],
    meta: {
      total: payload.total ?? 0,
      current_page: payload.current_page ?? 1,
      last_page: payload.last_page ?? 1,
      per_page: payload.per_page ?? 12,
    },
    prev_page_url: payload.prev_page_url ?? null,
    next_page_url: payload.next_page_url ?? null,
  };
}

async function fetchProducts(page = 1, q = '') {
  loading.value = true;
  try {
    const params = { page, per_page: 12 };
    if (q && q.trim().length) params.q = q.trim();
    const res = await apiGet('/products', params);
    const normalized = normalizePaginator(res.data);
    products.value = normalized;
    productPage.value = normalized.meta.current_page || page;
  } catch (err) {
    console.error(err);
    showNotice('Không thể tải danh sách sản phẩm', 'error');
    products.value = normalizePaginator(null);
  } finally {
    loading.value = false;
  }
}

function openProductForm(product = null) {
  if (product) {
    editingProduct.id = product.id;
    editingProduct.name = product.name || '';
    editingProduct.slug = product.slug || '';
    editingProduct.category_id = product.category_id ?? null;
    editingProduct.price = product.price ?? 0;
    editingProduct.original_price = product.original_price ?? null;
    editingProduct.short_description = product.short_description || '';
    editingProduct.description = product.description || '';
    editingProduct.image = product.image || '';
    editingProduct.is_best_seller = !!product.is_best_seller;
    productImageFile.value = null;
    previewUrl.value = editingProduct.image || null;
  } else {
    resetEditingProduct();
  }
  productFormVisible.value = true;
}

function closeProductForm() {
  productFormVisible.value = false;
  if (previewUrl.value && typeof previewUrl.value === 'string' && previewUrl.value.startsWith('blob:')) {
    try { URL.revokeObjectURL(previewUrl.value); } catch (e) {}
  }
  previewUrl.value = null;
  resetEditingProduct();
}

async function fetchReviews() {
  reviewLoading.value = true;
  try {
    const res = await apiGet('/reviews');
    reviews.value = res.data;
  } catch (err) {
    console.error(err);
    showNotice('Không thể tải đánh giá', 'error');
    reviews.value = [];
  } finally {
    reviewLoading.value = false;
  }
}

function openReviewForm(r = null) {
  editingReview.id = r?.id || null;
  editingReview.product_id = r?.product_id || null;
  editingReview.user_name = r?.user_name || '';
  editingReview.rating = r?.rating || 5;
  editingReview.comment = r?.comment || '';
  editingReview.active = r?.active ?? true;
  reviewFormVisible.value = true;
}

function closeReviewForm() {
  reviewFormVisible.value = false;
}

async function saveReview() {
  try {
    const payload = {
      product_id: editingReview.product_id,
      user_name: editingReview.user_name,
      rating: editingReview.rating,
      comment: editingReview.comment,
      active: editingReview.active ? 1 : 0,
    };

    if (editingReview.id) {
      await apiPut(`/reviews/${editingReview.id}`, payload);
      showNotice('Đánh giá đã được cập nhật', 'success');
    } else {
      await apiPost('/reviews', payload);
      showNotice('Đánh giá đã được tạo', 'success');
    }
    reviewFormVisible.value = false;
    fetchReviews();
  } catch (err) {
    console.error(err);
    showNotice('Không thể lưu đánh giá', 'error');
  }
}

async function deleteReview(id) {
  try {
    await apiDelete(`/reviews/${id}`);
    showNotice('Đánh giá đã được xóa', 'success');
    fetchReviews();
  } catch (err) {
    console.error(err);
    showNotice('Không thể xóa đánh giá', 'error');
  }
}

function resetEditingProduct() {
  if (previewUrl.value && typeof previewUrl.value === 'string' && previewUrl.value.startsWith('blob:')) {
    try { URL.revokeObjectURL(previewUrl.value); } catch (e) {}
  }
  previewUrl.value = null;

  editingProduct.id = null;
  editingProduct.name = '';
  editingProduct.slug = '';
  editingProduct.category_id = null;
  editingProduct.price = 0;
  editingProduct.original_price = null;
  editingProduct.short_description = '';
  editingProduct.description = '';
  editingProduct.image = '';
  editingProduct.is_best_seller = false;
  productImageFile.value = null;
}

function onProductImageSelected(e) {
  productImageFile.value = e.target.files?.[0] || null;
}

function clearImage() {
  if (previewUrl.value && typeof previewUrl.value === 'string' && previewUrl.value.startsWith('blob:')) {
    try { URL.revokeObjectURL(previewUrl.value); } catch (e) {}
  }
  productImageFile.value = null;
  editingProduct.image = '';
  previewUrl.value = null;
}

function validateProduct() {
  if (!editingProduct.name || editingProduct.name.trim().length < 2) {
    showNotice('Tên sản phẩm phải từ 2 ký tự trở lên', 'error');
    return false;
  }
  if (editingProduct.price == null || editingProduct.price < 0) {
    showNotice('Giá không hợp lệ', 'error');
    return false;
  }
  return true;
}
function isAbsoluteHttpUrl(value) {
  try {
    const u = new URL(value);
    return u.protocol === 'http:' || u.protocol === 'https:';
  } catch {
    return false;
  }
}

async function saveProduct() {
  if (!validateProduct()) return;
  loading.value = true;
  try {
    const fd = new FormData();
    fd.append('name', editingProduct.name);
    fd.append('category_id', editingProduct.category_id ?? '');
    fd.append('price', editingProduct.price);
    fd.append('short_description', editingProduct.short_description || '');
    fd.append('description', editingProduct.description || '');
    fd.append('is_best_seller', editingProduct.is_best_seller ? 1 : 0);

    if (productImageFile.value) {
      fd.append('image', productImageFile.value);
    } else if (editingProduct.image) {
      if (isAbsoluteHttpUrl(editingProduct.image)) {
        fd.append('image_url', editingProduct.image);
      } else {
        fd.append('existing_image', editingProduct.image);
      }
    }

    if (editingProduct.id) {
      fd.append('_method', 'PUT');
      const res = await apiPost(`/products/${editingProduct.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
      showNotice('Cập nhật sản phẩm thành công', 'success');
      console.log('save response', res.data);
    } else {
      const res = await apiPost('/products', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
      showNotice('Tạo sản phẩm mới thành công', 'success');
      console.log('save response', res.data);
    }

    await fetchProducts(productPage.value);
    closeProductForm();
  } catch (err) {
    console.error('saveProduct error', err.response?.data || err);
    const msg = err.response?.data?.message || 'Lỗi khi lưu sản phẩm';
    showNotice(msg, 'error');
  } finally {
    loading.value = false;
  }
}

async function deleteProduct(slug) {
  if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
  loading.value = true;
  try {
    await apiDelete(`/products/${slug}`);
    showNotice('Đã xóa sản phẩm', 'success');
    await fetchProducts(productPage.value);
  } catch (err) {
    console.error(err);
    showNotice('Xóa thất bại', 'error');
  } finally {
    loading.value = false;
  }
}

async function fetchCategories() {
  categoryLoading.value = true;
  try {
    const res = await apiGet('/categories');
    categories.value = Array.isArray(res.data) ? res.data : (res.data?.data ?? []);
  } catch (err) {
    console.error(err);
    showNotice('Không tải được danh mục', 'error');
    categories.value = [];
  } finally {
    categoryLoading.value = false;
  }
}

async function fetchReceipts() {
  receiptLoading.value = true
  try {
    const res = await axios.get('/receipts')
    receipts.value = Array.isArray(res.data) ? res.data : (res.data?.data ?? [])
  } catch (err) {
    console.error(err)
    showNotice('Không tải được hóa đơn', 'error')
    receipts.value = []
  } finally {
    receiptLoading.value = false
  }
}

function openCategoryForm(cat = null) {
  if (cat) {
    editingCategory.id = cat.id;
    editingCategory.name = cat.name || '';
    editingCategory.slug = cat.slug || '';
    editingCategory.image = cat.image || '';
    editingCategory.sort_order = cat.sort_order ?? 0;
    categoryImageFile.value = null;
  } else {
    resetEditingCategory();
  }
  categoryFormVisible.value = true;
}

function closeCategoryForm() {
  categoryFormVisible.value = false;
  resetEditingCategory();
}

function resetEditingCategory() {
  editingCategory.id = null;
  editingCategory.name = '';
  editingCategory.slug = '';
  editingCategory.image = '';
  editingCategory.sort_order = 0;
  categoryImageFile.value = null;
}

function onCategoryImageSelected(e) {
  categoryImageFile.value = e.target.files?.[0] || null;
}

function validateCategory() {
  if (!editingCategory.name || editingCategory.name.trim().length < 2) {
    showNotice('Tên danh mục không hợp lệ', 'error');
    return false;
  }
  return true;
}

async function saveCategory() {
  if (!validateCategory()) return;
  categoryLoading.value = true;
  try {
    const fd = new FormData();
    fd.append('name', editingCategory.name);
    fd.append('slug', editingCategory.slug ?? '');
    fd.append('sort_order', editingCategory.sort_order ?? 0);

    if (categoryImageFile.value) {
      fd.append('image', categoryImageFile.value);
    } else if (editingCategory.image) {
      fd.append('image', editingCategory.image);
    }

    if (editingCategory.id) {
      fd.append('_method', 'PUT');
      await apiPost(`/categories/${editingCategory.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
      showNotice('Cập nhật danh mục thành công', 'success');
    } else {
      await apiPost('/categories', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
      showNotice('Tạo danh mục thành công', 'success');
    }

    await fetchCategories();
    closeCategoryForm();
  } catch (err) {
    console.error(err);
    const msg = err.response?.data?.message || 'Lỗi khi lưu danh mục';
    showNotice(msg, 'error');
  } finally {
    categoryLoading.value = false;
  }
}

function viewReceipt(r) {
  selectedReceipt.value = r
  receiptModalVisible.value = true
}

async function deleteReceipt(id) {
  if (!confirm('Bạn có chắc muốn xóa hóa đơn này?')) return
  try {
    await apiDelete(`/receipts/${id}`)
    showNotice('Đã xóa hóa đơn', 'success')
    const res = await axios.get('/receipts')
    receipts.value = Array.isArray(res.data) ? res.data : (res.data?.data ?? [])
  } catch (err) {
    console.error(err)
    showNotice('Xóa hóa đơn thất bại', 'error')
  }
}

async function deleteCategory(id) {
  if (!confirm('Bạn có chắc muốn xóa danh mục này?')) return;
  categoryLoading.value = true;
  try {
    await apiDelete(`/categories/${id}`);
    showNotice('Đã xóa danh mục', 'success');
    await fetchCategories();
  } catch (err) {
    console.error(err);
    showNotice('Xóa danh mục thất bại', 'error');
  } finally {
    categoryLoading.value = false;
  }
}

function goToProductPage(p) {
  if (!p || p < 1) return;
  fetchProducts(p, ''); 
}
async function fetchBanners() {
  bannerLoading.value = true;
  try {
    const res = await apiGet('/banners');
    banners.value = res.data;
  } catch (err) {
    console.error(err);
    showNotice('Không thể tải banner', 'error');
    banners.value = [];
  } finally {
    bannerLoading.value = false;
  }
}

function openBannerForm(b = null) {
  editingBanner.id = b?.id || null;
  editingBanner.alt = b?.alt || '';
  editingBanner.sort_order = b?.sort_order || 0;
  editingBanner.active = b?.active ?? true;

  if (b?.image) {
    editingBanner.image = b.image;   
    bannerFile.value = null;       
  } else {
    editingBanner.image = '';
    bannerFile.value = null;
  }

  bannerFormVisible.value = true;
}


function closeBannerForm() {
  bannerFormVisible.value = false;
}

function onBannerImageSelected(e) {
  bannerFile.value = e.target.files[0];
}

async function saveBanner() {
  try {
    const formData = new FormData();

    if (bannerFile.value) {
      formData.append('image', bannerFile.value); // file upload
    } else if (editingBanner.image) {
      formData.append('image_url', editingBanner.image); // URL string
    }

    formData.append('sort_order', editingBanner.sort_order);
    formData.append('active', editingBanner.active ? 1 : 0);

    if (editingBanner.id) {
      await apiPost(`/banners/${editingBanner.id}?_method=PUT`, formData);
      showNotice('Banner đã được cập nhật', 'success');
    } else {
      await apiPost('/banners', formData);
      showNotice('Banner đã được tạo', 'success');
    }

    bannerFormVisible.value = false;
    fetchBanners();
  } catch (err) {
    console.error(err);
    showNotice('Không thể lưu banner', 'error');
  }
}


async function deleteBanner(id) {
  try {
    await apiDelete(`/banners/${id}`);
    showNotice('Banner đã được xóa', 'success');
    fetchBanners();
  } catch (err) {
    console.error(err);
    showNotice('Không thể xóa banner', 'error');
  }
}


const productCount = computed(() => products.value.data.length);
const categoryCount = computed(() => categories.value.length);
const receiptCount = computed(() => receipts.value.length)

watch(
  () => editingCategory.sort_order,
  (value) => {
    if (value < 0) editingCategory.sort_order = 0
    if (value >= categoryCount.value) editingCategory.sort_order = categoryCount.value - 1
  },
  { flush: 'sync' }
)


onMounted(() => {
  fetchProducts();   
  fetchCategories();
  fetchReceipts();
  fetchBanners();
  fetchReviews();
});

watch(activeTab, (t) => {
  if (t === 'products') fetchProducts();
  if (t === 'categories') fetchCategories();
  if (t === 'receipts') fetchReceipts();
});
</script>

<template>
  <Head title="Bảng điều khiển" />
  <AuthenticatedLayout>
    <div class="dashboard-wrap">
      <div class="container">
        <!-- Welcome card -->
        <section class="welcome-card">
          <div class="welcome-left">
            <h3>Chào mừng trở lại</h3>
            <p class="muted">Bạn đã đăng nhập! Hãy quản lý sản phẩm, danh mục, bài viết và banner từ trang quản trị.</p>
            <div class="welcome-actions">
              <button class="btn-accent" @click="openProductForm()">Sản phẩm mới</button>
              <button class="btn-accent-outline" @click="openCategoryForm()">Danh mục mới</button>
            </div>
          </div>

          <div class="welcome-stats">
            <div class="stat">
              <div class="stat-value">{{ productCount }}</div>
              <div class="stat-label">Sản phẩm</div>
            </div>
            <div class="stat">
              <div class="stat-value">{{ categoryCount }}</div>
              <div class="stat-label">Danh mục</div>
            </div>
            <div class="stat">
              <div class="stat-value">{{ receiptCount }}</div>
              <div class="stat-label">Hóa đơn</div>
            </div>
          </div>
        </section>

        <!-- Tabs -->
        <div style="display:flex; gap:8px; margin:18px 0;">
          <button :class="['btn-outline', { 'btn-primary': activeTab === 'products' }]" @click="activeTab = 'products'">Sản phẩm</button>
          <button :class="['btn-outline', { 'btn-primary': activeTab === 'categories' }]" @click="activeTab = 'categories'">Danh mục</button>
          <button :class="['btn-outline', { 'btn-primary': activeTab === 'banners' }]" @click="activeTab = 'banners'">Banner</button>
        </div>

        <!-- Products tab -->
        <section v-if="activeTab === 'products'" class="card" style="margin-bottom:18px;">
          <h4 style="margin-bottom:12px; color:var(--blue-500)">Sản phẩm</h4>

          <div v-if="loading" style="padding:12px;">Đang tải...</div>

          <table v-else style="width:100%; border-collapse:collapse;">
            <thead>
              <tr style="text-align:left; border-bottom:1px solid #eee;">
                <th style="padding:8px">Mã</th>
                <th style="padding:8px">Tên</th>
                <th style="padding:8px">Danh mục</th>
                <th style="padding:8px">Giá</th>
                <th style="padding:8px">Bán chạy</th>
                <th style="padding:8px">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in products.data" :key="p.id" style="border-bottom:1px solid #faf0f5;">
                <td style="padding:8px">{{ p.id }}</td>
                <td style="padding:8px">{{ p.name }}</td>
                <td style="padding:8px">{{ p.category?.name || '-' }}</td>
                <td style="padding:8px">{{ new Intl.NumberFormat('vi-VN').format(p.price) }}₫</td>
                <td style="padding:8px">{{ p.is_best_seller ? 'Có' : 'Không' }}</td>
                <td style="padding:8px">
                  <button @click="openProductForm(p)" style="margin-right:8px;">Sửa</button>
                  <button @click="deleteProduct(p.slug)" style="color:#c00;">Xóa</button>
                </td>
              </tr>
              <tr v-if="products.data.length === 0">
                <td colspan="6" style="padding:12px; color:var(--muted)">Không có sản phẩm.</td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
         <div v-if="products" style="margin-top:20px; display:flex; justify-content:center; align-items:center;">
              <button
                :disabled="!products.prev_page_url"
                @click="goToProductPage(products.meta.current_page - 1)"
                style="padding:8px 14px; border-radius:6px; color: white; background: #e75480; border:1px solid #ccc; cursor:pointer; font-size:14px; margin-right:12px;"
              >
                Trước
              </button>

              <span style="font-size:14px; font-weight:500; color:#444;">
                Trang {{ products.meta.current_page }} / {{ products.meta.last_page }}
              </span>

              <button
                :disabled="!products.next_page_url"
                @click="goToProductPage(products.meta.current_page + 1)"
                style="padding:8px 14px; border-radius:6px; color: white; background: #e75480; border:1px solid #ccc; cursor:pointer; font-size:14px; margin-left:12px;"
              >
                Sau
              </button>
            </div>
        </section>

        <!-- Categories tab -->
        <section v-if="activeTab === 'categories'" class="card" style="margin-bottom:18px;">
          <h4 style="margin-bottom:12px; color:var(--blue-500)">Danh mục</h4>

          <div v-if="categoryLoading" style="padding:12px;">Đang tải...</div>

          <table v-else style="width:100%; border-collapse:collapse;">
            <thead>
              <tr style="text-align:left; border-bottom:1px solid #eee;">
                <th style="padding:8px">Mã</th>
                <th style="padding:8px">Tên</th>
                <th style="padding:8px">Slug</th>
                <th style="padding:8px">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in categories" :key="c.id" style="border-bottom:1px solid #faf0f5;">
                <td style="padding:8px">{{ c.id }}</td>
                <td style="padding:8px">{{ c.name }}</td>
                <td style="padding:8px">{{ c.slug }}</td>
                <td style="padding:8px">
                  <button @click="openCategoryForm(c)" style="margin-right:8px;">Sửa</button>
                  <button @click="deleteCategory(c.id)" style="color:#c00;">Xóa</button>
                </td>
              </tr>
              <tr v-if="categories.length === 0">
                <td colspan="6" style="padding:12px; color:var(--muted)">Không có danh mục.</td>
              </tr>
            </tbody>
          </table>
        </section>
        
    <!-- Category modal -->
    <div v-if="categoryFormVisible" class="modal" style="position:fixed; inset:0; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.35); z-index:50;">
      <div style="background:white; width:520px; max-width:95%; border-radius:10px; padding:18px;">
        <h3 style="margin:0 0 12px;">{{ editingCategory.id ? 'Sửa danh mục' : 'Tạo danh mục' }}</h3>

        <div style="display:grid; grid-template-columns: 1fr; gap:10px;">
          <label style="display:block;">
            Tên
            <input v-model="editingCategory.name" style="width:100%; padding:8px; margin-top:6px;" />
          </label>
          <label style="display:block;">
                Thứ tự
                <input 
                  type="number"
                  v-model.number="editingCategory.sort_order"
                  :min="0"
                  :max="categoryCount - 1"
                  style="width:100%; padding:8px; margin-top:6px;"
                />
              </label>
        </div>

        <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:12px;">
          <button @click="closeCategoryForm" style="padding:8px 12px; border-radius:8px; background:#f0f0f0;">Hủy</button>
          <button @click="saveCategory" style="padding:8px 12px; border-radius:8px; background:linear-gradient(90deg,var(--blue-400),var(--blue-500)); color:white;">
            Lưu
          </button>
        </div>
      </div>
    </div>

>
              <!-- Cards grid -->
              <section class="cards-grid">
                <div class="card">
                  <h4>Thông tin website</h4>
                  <p class="muted">FASHION STYLE STORE</p>
                  <p class="muted">Địa chỉ: 180 Cao Lỗ, P.4, Q.8, TP.HCM.</p>
                </div>
              </section>
            </div>
          </div>
          <div v-if="reviewFormVisible" class="modal"
          style="position:fixed; inset:0; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.35); z-index:60;">
        <div style="background:white; width:520px; max-width:95%; border-radius:10px; padding:18px;">
          <h3 style="margin:0 0 12px;">{{ editingReview.id ? 'Sửa đánh giá' : 'Tạo đánh giá mới' }}</h3>

          <div style="display:grid; grid-template-columns: 1fr; gap:10px;">
            <label>
              Người dùng
              <input v-model="editingReview.user_name" style="width:100%; padding:8px; border:1px solid #eee; border-radius:6px;" />
            </label>
            <label>
              Sản phẩm ID
              <input v-model="editingReview.product_id" type="number" style="width:100%; padding:8px; border:1px solid #eee; border-radius:6px;" />
            </label>
            <label>
              Sao
              <input v-model="editingReview.rating" type="number" min="1" max="5" style="width:100%; padding:8px; border:1px solid #eee; border-radius:6px;" />
            </label>
            <label>
              Bình luận
              <textarea v-model="editingReview.comment" style="width:100%; padding:8px; border:1px solid #eee; border-radius:6px;"></textarea>
            </label>
            <label style="display:flex; align-items:center; gap:8px;">
              <input type="checkbox" v-model="editingReview.active" /> Hoạt động
            </label>
          </div>

          <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:12px;">
            <button @click="closeReviewForm" style="padding:8px 12px; border-radius:8px; background:#f0f0f0;">Hủy</button>
            <button @click="saveReview" style="padding:8px 12px; border-radius:8px; background:linear-gradient(90deg,var(--blue-400),var(--blue-500)); color:white;">Lưu</button>
          </div>
        </div>
      </div>
    <!-- Product modal -->
<div v-if="productFormVisible" class="modal" style="position:fixed; inset:0; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.45); z-index:70;">
  <div style="background:white;width:1000px;max-width:96%;border-radius:12px;padding:16px;box-shadow:0 20px 50px rgba(0,0,0,0.18);max-height:calc(100vh - 48px);overflow:visible;display:flex;flex-direction:column;">
    <!-- Header -->
    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px;">
      <h3 style="margin:0; font-size:18px; color:var(--pink-500);">
        {{ editingProduct.id ? 'Sửa sản phẩm' : 'Tạo sản phẩm mới' }}
      </h3>
      <div style="display:flex; gap:8px; align-items:center;">
        <button @click="closeProductForm" aria-label="Close" style="background:transparent; border:none; font-size:18px; cursor:pointer; color:#666;">✕</button>
      </div>
    </div>

    <!-- Body: left = form, right = preview -->
    <div style="display:grid; grid-template-columns: 1fr 360px; gap:18px; align-items:start;">
      <!-- Left: form -->
      <div style="min-width:0;">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
          <label style="display:block;">
            <div style="font-size:13px; color:var(--muted); margin-bottom:6px;">Tên</div>
            <input v-model="editingProduct.name" placeholder="Tên sản phẩm" style="width:100%; padding:10px; border-radius:8px; border:1px solid #eee; font-size:14px;" />
          </label>

          <label style="display:block;">
            <div style="font-size:13px; color:var(--muted); margin-bottom:6px;">Danh mục</div>
            <select v-model="editingProduct.category_id" style="width:100%; padding:10px; border-radius:8px; border:1px solid #eee; font-size:14px;">
              <option :value="null">-- không có --</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </label>

          <label style="display:block;">
            <div style="font-size:13px; color:var(--muted); margin-bottom:6px;">Giá (₫)</div>
            <input type="number" v-model.number="editingProduct.price" style="width:100%; padding:10px; border-radius:8px; border:1px solid #eee; font-size:14px;" />
          </label>

          <label style="display:flex; align-items:center; gap:8px; padding-top:6px;">
            <input type="checkbox" v-model="editingProduct.is_best_seller" style="width:18px; height:18px;" />
            <div style="font-size:13px; color:var(--muted);">Đánh dấu là bán chạy</div>
          </label>

          <div></div>
        </div>

        <!-- Image -->
        <div style="margin-top:14px;">
          <div style="font-size:13px; color:var(--muted); margin-bottom:6px;">Ảnh (URL hoặc tải lên)</div>
          <input v-model="editingProduct.image" placeholder="https://..." style="width:100%; padding:10px; border-radius:8px; border:1px solid #eee; font-size:14px;" />
          <div style="margin-top:8px; display:flex; gap:12px; align-items:center;">
            <input type="file" @change="onProductImageSelected" accept="image/*" />
            <div style="font-size:12px; color:#888;">Kích thước tối ưu: 800×800 · JPG/PNG</div>
            <button v-if="previewSrc" @click="clearImage" style="margin-left:auto; background:#fff; border:1px solid #eee; padding:6px 8px; border-radius:8px; cursor:pointer;">Xóa ảnh</button>
          </div>
        </div>

        <!-- Short description -->
        <div style="margin-top:14px;">
          <div style="font-size:13px; color:var(--muted); margin-bottom:6px;">Mô tả ngắn</div>
          <textarea v-model="editingProduct.short_description" rows="3" style="width:100%; padding:10px; border-radius:8px; border:1px solid #eee; font-size:14px;"></textarea>
        </div>

        <!-- Full description -->
        <div style="margin-top:14px;">
          <div style="font-size:13px; color:var(--muted); margin-bottom:6px;">Mô tả chi tiết (HTML được phép)</div>
          <textarea v-model="editingProduct.description" rows="3" style="width:100%; padding:10px; border-radius:8px; border:1px solid #eee; font-size:14px;"></textarea>
        </div>
      </div>

      <!-- Right: preview (sticky inside modal) -->
      <div style="align-self:start; position:sticky; top:20px;">
        <div style="width:340px; background:linear-gradient(180deg,#fff 0%,var(--pink-100) 100%); border-radius:10px; padding:14px; box-shadow:var(--card-shadow);">
          <div style="display:flex; gap:12px; align-items:flex-start;">
            <div style="width:140px; height:140px; border-radius:10px; overflow:hidden; background:#fafafa; display:flex; align-items:center; justify-content:center; border:1px solid #f3e9ee;">
              <img
                v-if="previewSrc"
                :src="previewSrc"
                alt="preview"
                style="width:100%; height:100%; object-fit:cover; display:block;"
              />
              <div v-else style="color:var(--muted); font-size:13px; padding:8px; text-align:center;">Ảnh xem trước</div>
            </div>

            <div style="flex:1; min-width:0;">
              <div style="font-weight:800; color:var(--pink-500); font-size:16px; line-height:1.1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                {{ editingProduct.name || 'Tên sản phẩm' }}
              </div>
              <div style="font-size:13px; color:#666; margin-top:8px; min-height:40px; overflow:hidden; text-overflow:ellipsis;">
                {{ editingProduct.short_description || 'Mô tả ngắn hiển thị ở đây.' }}
              </div>

              <div style="margin-top:12px; display:flex; gap:10px; align-items:center;">
                <div style="font-size:18px; font-weight:800; color:#111;">
                  {{ new Intl.NumberFormat('vi-VN').format(editingProduct.price || 0) }}₫
                </div>
              </div>
              <div v-if="editingProduct.is_best_seller" style="background:linear-gradient(90deg,var(--pink-400),var(--pink-500)); color:white; padding:6px 8px; border-radius:8px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="display:block;">
              <path d="M12 2l2.9 6.1L21 9.2l-5 3.9L17 21l-5-3.2L7 21l1-7.9-5-3.9 6.1-1.1L12 2z" fill="currentColor"/>
            </svg>
            Bán chạy
          </div>
            </div>
          </div>

          <div style="margin-top:12px; font-size:13px; color:#666;">
            <div><strong>Danh mục:</strong> {{ (categories.find(c => c.id === editingProduct.category_id)?.name) || '—' }}</div>
          </div>
        </div>

        <!-- Actions -->
        <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:14px;">
          <button @click="closeProductForm" style="padding:10px 14px; border-radius:8px; background:#f6f6f6; border:1px solid #eee; cursor:pointer;">Hủy</button>
          <button @click="saveProduct" style="padding:10px 14px; border-radius:8px; background:linear-gradient(90deg,var(--pink-400),var(--pink-500)); color:white; cursor:pointer;">Lưu</button>
        </div>
      </div>
    </div>
  </div>
</div>
    <!-- Category modal -->
    <div v-if="categoryFormVisible" class="modal" style="position:fixed; inset:0; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.35); z-index:50;">
      <div style="background:white; width:520px; max-width:95%; border-radius:10px; padding:18px;">
        <h3 style="margin:0 0 12px;">{{ editingCategory.id ? 'Sửa danh mục' : 'Tạo danh mục' }}</h3>

        <div style="display:grid; grid-template-columns: 1fr; gap:10px;">
          <label style="display:block;">
            Tên
            <input v-model="editingCategory.name" style="width:100%; padding:8px; margin-top:6px;" />
          </label>

          <label style="display:block;">
            Thứ tự
            <input type="number" v-model.number="editingCategory.sort_order" style="width:100%; padding:8px; margin-top:6px;" />
          </label>
        </div>

        <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:12px;">
          <button @click="closeCategoryForm" style="padding:8px 12px; border-radius:8px; background:#f0f0f0;">Hủy</button>
          <button @click="saveCategory" style="padding:8px 12px; border-radius:8px; background:linear-gradient(90deg,var(--blue-400),var(--blue-500)); color:white;">
            Lưu
          </button>
        </div>
      </div>
    </div>
    <!-- Receipt detail modal -->
<div v-if="receiptModalVisible" class="modal" style="position:fixed; inset:0; background:rgba(0,0,0,0.45); display:flex; align-items:center; justify-content:center; z-index:80;">
  <div style="background:white; width:800px; max-width:96%; border-radius:12px; padding:20px; box-shadow:0 20px 50px rgba(0,0,0,0.18); max-height:calc(100vh - 48px); overflow:auto;">
    <h3 style="margin-bottom:12px; color:var(--blue-500);">Chi tiết hóa đơn #{{ selectedReceipt?.id }}</h3>

    <div style="margin-bottom:12px; font-size:14px; color:#444;">
      <div><strong>Người dùng:</strong> {{ selectedReceipt?.user?.name || '—' }}</div>
      <div><strong>Trạng thái:</strong> {{ selectedReceipt?.status }}</div>
      <div><strong>Tổng tiền:</strong> {{ new Intl.NumberFormat('vi-VN').format(selectedReceipt?.total || 0) }}₫</div>
      <div><strong>Ngày tạo:</strong> {{ new Date(selectedReceipt?.created_at).toLocaleString('vi-VN') }}</div>
    </div>

    <table style="width:100%; border-collapse:collapse;">
      <thead>
        <tr style="text-align:left; border-bottom:1px solid #eee;">
          <th style="padding:8px">Sản phẩm</th>
          <th style="padding:8px">Giá</th>
          <th style="padding:8px">Số lượng</th>
          <th style="padding:8px">Tổng</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in selectedReceipt?.items" :key="item.id" style="border-bottom:1px solid #faf0f5;">
          <td style="padding:8px">{{ item.name }}</td>
          <td style="padding:8px">{{ new Intl.NumberFormat('vi-VN').format(item.price) }}₫</td>
          <td style="padding:8px">{{ item.quantity }}</td>
          <td style="padding:8px">{{ new Intl.NumberFormat('vi-VN').format(item.price * item.quantity) }}₫</td>
        </tr>
      </tbody>
    </table>

    <div style="display:flex; justify-content:flex-end; margin-top:18px;">
      <button @click="receiptModalVisible = false" style="padding:10px 14px; border-radius:8px; background:#f6f6f6; border:1px solid #eee; cursor:pointer;">Đóng</button>
    </div>
  </div>
</div>
    <!-- Notification -->
    <div
      v-if="notification.visible"
      :style="{
        position: 'fixed',
        right: '20px',
        bottom: '20px',
        background: notification.type === 'error' ? '#ffdddd' : '#e6ffef',
        color: notification.type === 'error' ? '#900' : '#064',
        padding: '10px 14px',
        borderRadius: '8px',
        boxShadow: '0 6px 18px rgba(0,0,0,0.08)'
      }"
    >
      {{ notification.message }}
    </div>
  </AuthenticatedLayout>
</template>

<style>
/* Biến chủ đề */
:root{
  --blue-100: #f0f8ff;
  --blue-200: #e6f7ff;
  --blue-300: #99ccff;
  --blue-400: #4fb0e6;
  --blue-500: #1e88e5;
  --white: #ffffff;
  --muted: #777;
  --card-shadow: 0 6px 18px rgba(30,136,229,0.08);
  --radius: 12px;
}

/* Bố cục */
.dashboard-header{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:16px;
  padding:18px 24px;
  background: var(--white);
  border-bottom: 1px solid rgba(30,136,229,0.08);
}
.dashboard-title{
  margin:0;
  font-size:20px;
  color:var(--blue-500);
  font-weight:700;
}
.header-actions{ display:flex; gap:10px; align-items:center; }

/* Container */
.dashboard-wrap{
  background: linear-gradient(180deg, var(--blue-100) 0%, #fff 60%);
  min-height: calc(100vh - 80px);
  padding: 28px 16px;
}
.container{
  max-width:1100px;
  margin:0 auto;
}

/* Thẻ chào mừng */
.welcome-card{
  display:flex;
  gap:20px;
  background: var(--white);
  border-radius: var(--radius);
  padding:20px;
  box-shadow: var(--card-shadow);
  align-items:center;
  justify-content:space-between;
  margin-bottom:24px;
}
.welcome-left h3{
  margin:0 0 6px 0;
  color:var(--blue-500);
  font-size:22px;
}
.muted{ color:var(--muted); margin:0 0 12px 0; }
.welcome-actions{ display:flex; gap:10px; flex-wrap:wrap; }
.btn-accent{
  background: linear-gradient(90deg,var(--blue-400),var(--blue-500));
  color:white;
  border:none;
  padding:10px 14px;
  border-radius:8px;
  cursor:pointer;
  font-weight:600;
}
.btn-accent-outline{
  background:transparent;
  color:var(--blue-500);
  border:2px solid rgba(30,136,229,0.12);
  padding:8px 12px;
  border-radius:8px;
  cursor:pointer;
  font-weight:600;
}

/* Thống kê */
.welcome-stats{ display:flex; gap:14px; align-items:center; }
.stat{ text-align:center; padding:10px 14px; background:var(--blue-100); border-radius:10px; min-width:88px; }
.stat-value{ font-size:20px; color:var(--blue-500); font-weight:700; }
.stat-label{ font-size:13px; color:var(--muted); }

/* Lưới thẻ */
.cards-grid{
  display:grid;
  grid-template-columns: repeat(3, 1fr);
  gap:18px;
}
.card{
  background:var(--white);
  border-radius:10px;
  padding:16px;
  box-shadow: var(--card-shadow);
}
.card h4{ margin:0 0 8px 0; color:var(--blue-500); }
.activity-list{ list-style:none; padding:0; margin:0; color:var(--muted); }
.activity-list li{ padding:8px 0; border-bottom:1px dashed rgba(0,0,0,0.04); }
.quick-links{ list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:8px; }
.quick-links a{ color:var(--blue-500); text-decoration:none; font-weight:600; cursor:pointer; }

/* Nút dùng trong header */
.btn-primary{
  background:var(--blue-300);
  color:white;
  border:none;
  padding:8px 12px;
  border-radius:8px;
  cursor:pointer;
  font-weight:600;
}
.btn-outline{
  background:transparent;
  color:var(--blue-500);
  border:1px solid rgba(30,136,229,0.12);
  padding:8px 12px;
  border-radius:8px;
  cursor:pointer;
}


/* Responsive */
@media (max-width: 900px){
  .cards-grid{ grid-template-columns: 1fr; }
  .welcome-card{ flex-direction:column; align-items:flex-start; }
  .welcome-stats{ width:100%; justify-content:space-between; }
  .dashboard-wrap{ padding:20px 12px; }
}
</style>
