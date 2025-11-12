
<template>
  <div>
    <h1>Danh sách sản phẩm</h1>

    <!-- Form thêm -->
    <form @submit.prevent="addProduct">
      <input v-model="newName" placeholder="Tên sản phẩm" />
      <button type="submit">Thêm</button>
    </form>

    <ul>
      <li v-for="product in products" :key="product.id">
        {{ product.name }}
        <button @click="editProduct(product)">Sửa</button>
        <button @click="deleteProduct(product.id)">Xoá</button>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

// Nhận props từ server (Laravel Controller)
const props = defineProps({
  products: {
    type: Array,
    default: () => []
  }
})

// state cho ô input
const newName = ref('')

// thêm
function addProduct() {
  if (!newName.value.trim()) return
  router.post('/products', { name: newName.value }, {
    onSuccess: () => { newName.value = '' }
  })
}

// sửa (dùng prompt cho nhanh)
function editProduct(product) {
  const name = prompt('Tên mới:', product.name)
  if (name && name.trim()) {
    router.put(`/products/${product.id}`, { name })
  }
}

// xoá
function deleteProduct(id) {
  if (confirm('Xoá sản phẩm này?')) {
    router.delete(`/products/${id}`)
  }
}
</script>
