<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
// Dữ liệu sản phẩm mẫu
const products = [
    { id: 1, name: 'Áo Thun Phong Cách', price: 299000, description: 'Vải cotton thoáng mát, kiểu dáng hiện đại, thoải mái cho mọi hoạt động.', image_url: 'https://placehold.co/400x300/6366f1/ffffff?text=Shirt' },
    { id: 2, name: 'Giày Thể Thao Cao Cấp', price: 1500000, description: 'Thiết kế ôm chân, đế chống trượt, phù hợp cho chạy bộ và tập luyện.', image_url: 'https://placehold.co/400x300/34d399/ffffff?text=Shoes' },
    { id: 3, name: 'Đồng Hồ Thông Minh', price: 3499000, description: 'Màn hình AMOLED, theo dõi sức khỏe, pin bền bỉ 7 ngày.', image_url: 'https://placehold.co/400x300/f87171/ffffff?text=Watch' },
    { id: 4, name: 'Tai Nghe Không Dây Pro', price: 950000, description: 'Âm thanh Hifi, công nghệ khử tiếng ồn, pin dùng liên tục 20 giờ.', image_url: 'https://placehold.co/400x300/fb923c/ffffff?text=Headphones' },
];

// Biến trạng thái phản ứng (Reactive State)
const cartCount = ref(0);
const showMessage = ref({ active: false, text: '' });

// Phương thức định dạng giá tiền
const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
};

// Phương thức xử lý thêm vào giỏ hàng
const addToCart = (product) => {
    cartCount.value += 1;
    
    // Hiển thị thông báo
    showMessage.value.text = `Đã thêm "${product.name}" vào giỏ hàng!`;
    showMessage.value.active = true;

    setTimeout(() => {
        showMessage.value.active = false;
    }, 3000);
};

// Phương thức xử lý xem giỏ hàng
const viewCart = () => {
    showMessage.value.text = cartCount.value === 0 ? 
        "Giỏ hàng của bạn đang trống." : 
        `Bạn có ${cartCount.value} sản phẩm trong giỏ hàng.`;
    showMessage.value.active = true;

    setTimeout(() => {
        showMessage.value.active = false;
    }, 3000);
};
</script>

<template>
    <div class="min-h-screen flex flex-col font-inter bg-gray-50">
        
        <!-- CUSTOM NOTIFICATION/MESSAGE BOX -->
        <div v-if="showMessage.active" 
             :class="{ 'opacity-100 transform translate-y-0': showMessage.active }" 
             class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 transition-all duration-300 bg-green-600 text-white p-4 rounded-lg shadow-2xl font-semibold text-center max-w-xs sm:max-w-md">
            {{ showMessage.text }}
        </div>

        <!-- HEADER -->
        <header class="bg-white shadow-md sticky top-0 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo / Tên Cửa Hàng -->
                    <div class="flex-shrink-0">
                        <a href="#" class="text-2xl font-bold text-indigo-600 tracking-wider">
                            SHOP<span class="text-red-500">VIET</span>
                        </a>
                    </div>

                    <!-- Navigation -->
                    <nav class="hidden md:flex space-x-8">
                        <a href="#" class="text-gray-600 hover:text-indigo-600 transition duration-150 font-medium">Trang Chủ</a>
                        <a href="#" class="text-gray-600 hover:text-indigo-600 transition duration-150 font-medium">Sản Phẩm</a>
                        <a href="#" class="text-gray-600 hover:text-indigo-600 transition duration-150 font-medium">Tin Tức</a>
                        <a href="#" class="text-gray-600 hover:text-indigo-600 transition duration-150 font-medium">Liên Hệ</a>
                    </nav>

                    <!-- Giỏ Hàng Icon -->
                    <div class="flex items-center">
                        <button @click="viewCart" class="p-2 relative rounded-full hover:bg-gray-100 transition duration-150">
                            <!-- Icon Giỏ Hàng (Inline SVG) -->
                            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <!-- Số lượng trong giỏ -->
                            <span v-if="cartCount > 0" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                                {{ cartCount }}
                            </span>
                        </button>
                        <nav v-if="canLogin" class="-mx-3 flex flex-1 justify-end">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                        >
                            Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="rounded-md px-3 py-2 text-black hover:text-gray-700 focus:outline-none focus-visible:ring-[#FF2D20]"
                            >
                                Log in
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="rounded-md px-3 py-2 text-black hover:text-gray-700 focus:outline-none focus-visible:ring-[#FF2D20]"
                            >
                                Register
                            </Link>
                        </template>

                    </nav>
                    </div>
                </div>
            </div>
        </header>

        <!-- HERO SECTION (Banner) -->
        <main class="flex-grow">
            <section class="bg-indigo-500 h-96 flex items-center justify-center p-4 shadow-lg mb-10">
                <div class="text-center">
                    <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold text-white mb-4 text-shadow">
                        GIẢM GIÁ LỚN! LÊN ĐẾN 50%
                    </h1>
                    <p class="text-xl sm:text-2xl text-indigo-100 mb-8 font-light text-shadow">
                        Khám phá các sản phẩm chất lượng cao ngay hôm nay.
                    </p>
                    <a href="#" class="inline-block px-8 py-3 bg-white text-indigo-600 font-bold rounded-full shadow-xl hover:bg-gray-100 transform transition duration-300 hover:scale-105">
                        MUA SẮM NGAY
                    </a>
                </div>
            </section>

            <!-- PRODUCT GRID SECTION -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Sản Phẩm Nổi Bật</h2>

                <!-- Product Grid (Responsive layout) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Product Card (Loop with v-for) -->
                    <div v-for="product in products" :key="product.id" class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition duration-300 transform hover:-translate-y-1 flex flex-col">
                        
                        <!-- Product Image Placeholder -->
                        <div class="h-48 bg-gray-200 flex items-center justify-center text-center text-gray-500 font-medium p-4">
                            <img :src="product.image_url" :alt="product.name" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='https://placehold.co/400x300/a3a3a3/ffffff?text=Product+Image';" />
                        </div>

                        <div class="p-5 flex-grow flex flex-col">
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ product.name }}</h3>
                            <p class="text-sm text-gray-500 mb-3 flex-grow">{{ product.description }}</p>
                            
                            <div class="flex justify-between items-center mt-auto pt-3 border-t border-gray-100">
                                <span class="text-2xl font-bold text-red-600">
                                    {{ formatPrice(product.price) }}
                                </span>
                                <button @click="addToCart(product)"
                                        class="bg-indigo-600 text-white px-4 py-2 rounded-full text-sm font-medium shadow-md hover:bg-indigo-700 transition duration-150 transform hover:scale-105 active:scale-95">
                                    <!-- Icon Thêm (Inline SVG) -->
                                    <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Thêm vào giỏ
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- End Product Card -->
                </div>
            </section>
        </main>

        <!-- FOOTER -->
        <footer class="bg-gray-800 text-white mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center">
                <div class="flex justify-center space-x-6 mb-4">
                    <a href="#" class="text-gray-300 hover:text-white transition duration-150">Về Chúng Tôi</a>
                    <a href="#" class="text-gray-300 hover:text-white transition duration-150">Điều Khoản</a>
                    <a href="#" class="text-gray-300 hover:text-white transition duration-150">Chính Sách Bảo Mật</a>
                </div>
                <p class="text-gray-400 text-sm">
                    Bản quyền &copy; 2024 Cửa Hàng Đặc Biệt.
                    <br class="sm:hidden">
                    Được phát triển với Vue 3 và Tailwind CSS.
                </p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Thiết lập font Inter cho toàn bộ trang */
.font-inter {
    font-family: 'Inter', sans-serif;
}

/* Định nghĩa đổ bóng cho chữ (giống như trong file HTML) */
.text-shadow {
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4);
}
</style>