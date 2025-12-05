<template>
  <div class="min-h-screen bg-gray-50 font-sans text-gray-800">
    <header class="flex justify-between items-center bg-gradient-to-r from-gray-800 to-gray-900 text-white px-6 py-4 shadow-md z-30 relative">
      <div>
        <h1 class="text-xl font-bold tracking-wide uppercase">Fashion Store Admin</h1>
        <p class="text-xs mt-1 text-gray-400">LARAVEL + VUE SYSTEM</p>
      </div>
      
      <div class="relative">
        <div @click="toggleMenu" class="flex items-center space-x-3 cursor-pointer select-none hover:opacity-80 transition-opacity">
          <div class="w-10 h-10 bg-white text-gray-900 font-bold rounded-full flex items-center justify-center border-2 border-gray-700">
            {{ user.name ? user.name.charAt(0).toUpperCase() : 'A' }}
          </div>
          <div class="text-right hidden md:block">
            <div class="font-semibold text-sm text-white">{{ user.name }}</div>
            <div class="text-xs text-gray-400">Administrator</div>
          </div>
          <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </div>

        <div v-if="showMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 ring-1 ring-black ring-opacity-5 origin-top-right focus:outline-none">
          <Link 
            :href="route('profile.edit')" 
            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out"
            @click="showMenu = false"
          >
            Profile
          </Link>

          <div class="border-t border-gray-100 my-1"></div>

          <Link 
            :href="route('logout')" 
            method="post" 
            as="button"
            class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium transition duration-150 ease-in-out"
          >
            Log Out
          </Link>
        </div>
      </div>
    </header>

    <div class="flex h-[calc(100vh-4.5rem)]">
      <aside class="w-64 bg-white border-r flex-shrink-0 flex flex-col h-full overflow-y-auto">
        <nav class="flex flex-col space-y-2 p-4 text-gray-600 font-medium text-sm">
          <button @click="setCurrentView('home')" :class="getMenuClass('home')">
            <span>📊</span> <span class="ml-2">Tổng quan</span>
          </button>
          
          <div class="text-xs font-bold text-gray-400 uppercase mt-4 mb-2 px-3">Quản lý</div>
          
          <button @click="setCurrentView('products')" :class="getMenuClass('products')">
            <span>🧵</span> <span class="ml-2">Sản phẩm</span>
          </button>
          <button @click="setCurrentView('orders')" :class="getMenuClass('orders')">
            <span>📦</span> <span class="ml-2">Đơn hàng</span>
          </button>
          <button @click="setCurrentView('inventory')" :class="getMenuClass('inventory')">
            <span>👚</span> <span class="ml-2">Tồn kho</span>
          </button>
        </nav>
      </aside>

      <main class="flex-1 p-8 overflow-auto bg-gray-50">
        
        <div v-if="currentView === 'home'">
          <h2 class="text-2xl font-bold text-gray-800 mb-6">TỔNG QUAN KINH DOANH</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
              <p class="text-sm font-medium text-gray-500">Doanh thu (Đã hoàn thành)</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatPrice(stats.revenue) }}</p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
              <p class="text-sm font-medium text-gray-500">Đơn hàng chờ xử lý</p>
              <p class="text-2xl font-bold text-indigo-600 mt-1">{{ stats.pendingOrders }}</p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
              <p class="text-sm font-medium text-gray-500">Tổng sản phẩm</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.totalProducts }}</p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
              <p class="text-sm font-medium text-gray-500">Sắp hết hàng (< 10)</p>
              <p class="text-2xl font-bold text-red-600 mt-1">{{ stats.lowStock }}</p>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Đơn hàng mới nhất</h3>
            <div class="overflow-x-auto">
              <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                  <tr>
                    <th class="px-4 py-3">Mã đơn</th>
                    <th class="px-4 py-3">Khách hàng</th>
                    <th class="px-4 py-3">Ngày đặt</th>
                    <th class="px-4 py-3">Tổng tiền</th>
                    <th class="px-4 py-3">Trạng thái</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="order in orders.slice(0, 5)" :key="order.order_id" class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-indigo-600">#{{ order.order_id }}</td>
                    <td class="px-4 py-3">{{ order.fullname }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ formatDate(order.order_date) }}</td>
                    <td class="px-4 py-3 font-medium">{{ formatPrice(order.total_price) }}</td>
                    <td class="px-4 py-3">
                      <span :class="getStatusClass(order.status)" class="px-2 py-1 rounded-full text-xs font-semibold">
                        {{ mapStatus(order.status) }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div v-if="currentView === 'products'">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">DANH SÁCH SẢN PHẨM</h2>
            <button @click="openForm()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
              + Thêm sản phẩm
            </button>
          </div>

          <div v-if="showForm" class="mb-6 bg-white p-4 rounded-lg shadow-md border-t-4 border-indigo-500">
            <h3 class="text-xl font-bold mb-4 text-gray-800">{{ form.product_id ? 'CẬP NHẬT SẢN PHẨM' : 'THÊM SẢN PHẨM MỚI' }}</h3>
            <form @submit.prevent="saveProduct" class="space-y-4">
              <input v-model="form.product_name" placeholder="Tên sản phẩm" class="border border-gray-300 p-2 w-full rounded focus:ring-indigo-500 focus:border-indigo-500" required />
              <input v-model="form.category_name" placeholder="Danh mục (Ví dụ: Áo, Quần, Váy)" class="border border-gray-300 p-2 w-full rounded focus:ring-indigo-500 focus:border-indigo-500" required />
              
              <div class="grid grid-cols-2 gap-4">
                  <input v-model.number="form.price" type="number" placeholder="Giá bán (VND)" class="border border-gray-300 p-2 w-full rounded focus:ring-indigo-500 focus:border-indigo-500" min="1000" required />
                  <input v-model.number="form.stock" type="number" placeholder="Tồn kho" class="border border-gray-300 p-2 w-full rounded focus:ring-indigo-500 focus:border-indigo-500" min="0" required />
              </div>

              <input v-model="form.image" type="text" placeholder="URL hình ảnh (Tùy chọn)" class="border border-gray-300 p-2 w-full rounded focus:ring-indigo-500 focus:border-indigo-500" />

              <div class="flex space-x-3 pt-2">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                  {{ form.product_id ? '✅ Cập nhật' : '➕ Thêm sản phẩm' }}
                </button>
                <button type="button" @click="closeForm" class="bg-gray-500 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-gray-600 transition">Hủy</button>
              </div>
            </form>
          </div>

          <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sản phẩm</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Danh mục</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Giá bán</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tồn kho</th>
                  <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Hành động</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="p in products" :key="p.product_id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="h-10 w-10 bg-gray-100 rounded flex-shrink-0 flex items-center justify-center overflow-hidden">
                        <img v-if="p.image" :src="p.image" class="h-full w-full object-cover" />
                        <span v-else class="text-xs text-gray-400">No img</span>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ p.product_name }}</div>
                        <div class="text-xs text-gray-500">ID: {{ p.product_id }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ p.category_name || 'Chưa phân loại' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ formatPrice(p.price) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span :class="p.stock < 10 ? 'text-red-600 font-bold' : 'text-gray-900'">
                      {{ p.stock }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                    <button @click="editProduct(p)" class="text-indigo-600 hover:text-indigo-900 mr-3">Sửa</button>
                    <button @click="deleteProduct(p.product_id)" class="text-red-600 hover:text-red-900">Xóa</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div v-if="currentView === 'orders'">
          <h2 class="text-2xl font-bold text-gray-800 mb-6">QUẢN LÝ ĐƠN HÀNG</h2>
          <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Mã đơn</th>
                  <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Khách hàng</th>
                  <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Ngày đặt</th>
                  <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Tổng tiền</th>
                  <th class="px-6 py-3 text-center font-medium text-gray-500 uppercase">Trạng thái</th>
                  <th class="px-6 py-3 text-center font-medium text-gray-500 uppercase">Thao tác</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="order in orders" :key="order.order_id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 font-bold text-indigo-600">#{{ order.order_id }}</td>
                  <td class="px-6 py-4">
                    <div class="text-gray-900 font-medium">{{ order.fullname }}</div>
                    <div class="text-xs text-gray-500">ID KH: {{ order.user_id }}</div>
                  </td>
                  <td class="px-6 py-4 text-gray-500">{{ formatDate(order.order_date) }}</td>
                  <td class="px-6 py-4 font-medium">{{ formatPrice(order.total_price) }}</td>
                  <td class="px-6 py-4 text-center">
                    <span :class="getStatusClass(order.status)" class="px-3 py-1 rounded-full text-xs font-semibold">
                      {{ mapStatus(order.status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <button class="text-blue-600 hover:text-blue-800 border border-blue-600 px-2 py-1 rounded text-xs">
                      Chi tiết
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        
        <div v-if="currentView === 'inventory'">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">KHO HÀNG</h2>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div v-for="p in products" :key="p.product_id" class="bg-white p-4 rounded shadow border-l-4" :class="p.stock < 10 ? 'border-red-500' : 'border-green-500'">
                      <h3 class="font-bold text-gray-800">{{ p.product_name }}</h3>
                      <div class="mt-2 flex justify-between items-end">
                          <div>
                              <p class="text-xs text-gray-500">Giá vốn: {{ formatPrice(p.price * 0.7) }}</p> <p class="text-xs text-gray-500">Giá bán: {{ formatPrice(p.price) }}</p>
                          </div>
                          <div class="text-right">
                              <p class="text-2xl font-bold" :class="p.stock < 10 ? 'text-red-600' : 'text-gray-800'">{{ p.stock }}</p>
                              <p class="text-xs text-gray-500">Tồn kho</p>
                          </div>
                      </div>
                  </div>
              </div>
          </div>

      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue'; // Đã BỔ SUNG 'reactive'
import { Link, usePage } from '@inertiajs/vue3'; // Import Inertia tools

// --- State Variables ---
const currentView = ref('home');
const showMenu = ref(false);

// *** USER LOGIC ***
const page = usePage();
const user = computed(() => page.props.auth?.user || { name: 'Admin User' });

// --- CRUD STATE (BỔ SUNG) ---
const showForm = ref(false); // Điều khiển hiển thị Form Thêm/Sửa
// Đối tượng form để binding dữ liệu
const form = reactive({
    product_id: null,
    product_name: '',
    category_name: '',
    price: 0,
    stock: 0,
    image: null,
});

// --- DATA & STATS ---

const stats = ref({
    revenue: 0,
    pendingOrders: 0,
    totalProducts: 0,
    lowStock: 0
});

const products = ref([]);
const orders = ref([]);


// --- STATS UPDATE ---
// Hàm tính toán lại các chỉ số thống kê
const updateStats = () => {
    const totalProducts = products.value.length;
    const lowStock = products.value.filter(p => p.stock < 10).length;
    const completedRevenue = orders.value
        .filter(o => o.status === 'completed')
        .reduce((sum, order) => sum + order.total_price, 0);
    const pendingOrders = orders.value.filter(o => o.status === 'pending').length;

    stats.value = {
        revenue: completedRevenue,
        pendingOrders: pendingOrders,
        totalProducts: totalProducts,
        lowStock: lowStock
    };
};

// --- CORE CRUD FUNCTIONS ---

const openForm = () => {
    // Reset form để thêm mới
    Object.assign(form, {
        product_id: null,
        product_name: '',
        category_name: '',
        price: 0,
        stock: 0,
        image: null,
    });
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
};

const editProduct = (product) => {
    // Nạp dữ liệu sản phẩm vào form để chỉnh sửa
    Object.assign(form, product);
    showForm.value = true;
};

const saveProduct = () => {
    if (!form.product_name || form.price <= 0 || form.stock < 0) {
        alert("Vui lòng nhập đầy đủ Tên sản phẩm, Giá (> 0) và Tồn kho (>= 0).");
        return;
    }

    if (form.product_id) {
        // Cập nhật (Update)
        const index = products.value.findIndex(p => p.product_id === form.product_id);
        if (index !== -1) {
            products.value[index] = { ...form };
        }
    } else {
        // Thêm mới (Create)
        const newId = Math.max(0, ...products.value.map(p => p.product_id)) + 1;
        const newProduct = {
            ...form,
            product_id: newId,
        };
        products.value.push(newProduct);
    }

    closeForm();
    updateStats(); // Cập nhật lại thống kê sau khi thay đổi
    alert(`Đã ${form.product_id ? 'Cập nhật' : 'Thêm mới'} sản phẩm thành công.`);
};

const deleteProduct = (id) => {
    if (confirm(`Bạn có chắc chắn muốn XÓA sản phẩm ID ${id} này không?`)) {
        products.value = products.value.filter(p => p.product_id !== id);
        updateStats(); // Cập nhật lại thống kê sau khi thay đổi
        alert(`Đã Xóa sản phẩm ID: ${id}`);
    }
};

// --- MOCK DATA FETCHING ---
const fetchData = async () => {
    try {
        console.log("Loading mock data...");
        // 1. Mock Products
        products.value = [
            {
                product_id: 1,
                product_name: 'Áo Thun Basic',
                category_name: 'Áo',
                price: 150000,
                stock: 50,
                image: null
            },
            {
                product_id: 2,
                product_name: 'Quần Jean Slimfit',
                category_name: 'Quần',
                price: 450000,
                stock: 5,
                image: null
            },
            {
                product_id: 3,
                product_name: 'Váy Hoa Nhí',
                category_name: 'Váy',
                price: 320000,
                stock: 12,
                image: null
            }
        ];

        // 2. Mock Orders
        orders.value = [
            {
                order_id: 101,
                user_id: 1,
                fullname: 'KHUU NGOC THANH PHUONG',
                total_price: 600000,
                order_date: '2025-11-25 14:30:00',
                status: 'pending'
            },
            {
                order_id: 100,
                user_id: 2,
                fullname: 'Nguyen Van A',
                total_price: 1250000,
                order_date: '2025-11-24 09:15:00',
                status: 'completed'
            },
            {
                order_id: 99,
                user_id: 3,
                fullname: 'Tran Thi B',
                total_price: 450000,
                order_date: '2025-11-23 18:00:00',
                status: 'canceled'
            }
        ];

        // 3. Mock Stats: Gọi hàm updateStats để tính toán động
        updateStats();

    } catch (error) {
        console.error("Error loading data:", error);
    }
};

onMounted(() => {
    fetchData();
});

// --- HELPER FUNCTIONS ---

const setCurrentView = (view) => {
    currentView.value = view;
};

const toggleMenu = () => {
    showMenu.value = !showMenu.value;
};

const getMenuClass = (viewName) => {
    const base = "flex items-center px-3 py-2 rounded-lg transition-colors duration-200 w-full text-left";
    return currentView.value === viewName 
        ? `${base} bg-indigo-50 text-indigo-700 font-bold` 
        : `${base} hover:bg-gray-100 text-gray-600`;
};

const formatPrice = (value) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
};

const formatDate = (dateString) => {
    if(!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'});
};

const mapStatus = (status) => {
    const map = {
        'pending': 'Chờ xử lý',
        'confirmed': 'Đã xác nhận',
        'shipping': 'Đang giao',
        'completed': 'Hoàn thành',
        'canceled': 'Đã hủy'
    };
    return map[status] || status;
};

const getStatusClass = (status) => {
    switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-700';
        case 'confirmed': return 'bg-indigo-100 text-indigo-700';
        case 'shipping': return 'bg-blue-100 text-blue-700';
        case 'completed': return 'bg-green-100 text-green-700';
        case 'canceled': return 'bg-red-100 text-red-700';
        default: return 'bg-gray-100 text-gray-600';
    }
};
</script>

<style scoped>
/* Custom Scrollbar */
::-webkit-scrollbar {
  width: 8px;
}
::-webkit-scrollbar-track {
  background: #f1f1f1; 
}
::-webkit-scrollbar-thumb {
  background: #c7c7c7; 
  border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8; 
}
</style>