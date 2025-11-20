<template>
  <div>
    <h1>Categories</h1>

    <!-- Form thêm category -->
    <form @submit.prevent="addCategory">
      <input v-model="newCategory" placeholder="Category name" />
      <textarea v-model="description" placeholder="Description"></textarea>
      <button type="submit">Add</button>
    </form>

    <!-- Hiển thị thông báo -->
    <p v-if="$page.props.flash?.success" style="color:green">
      {{ $page.props.flash.success }}
    </p>

    <!-- Danh sách category -->
    <ul>
      <li v-for="cat in categories" :key="cat.category_id">
        {{ cat.category_name }} - {{ cat.description }}
      </li>
    </ul>
  </div>
</template>

<script>
import { Inertia } from '@inertiajs/inertia'

export default {
  props: {
    categories: Array
  },
  data() {
    return {
      newCategory: '',
      description: ''
    }
  },
  methods: {
    addCategory() {
      Inertia.post(this.route('categories.store'), {
        category_name: this.newCategory,
        description: this.description
      })
      this.newCategory = ''
      this.description = ''
    }
  }
}
</script>
