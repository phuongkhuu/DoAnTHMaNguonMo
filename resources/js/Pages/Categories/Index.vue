<template>
  <div style="padding: 20px">
    <h1>Category Management</h1>

    <!-- Form thêm category -->
    <form @submit.prevent="submitAdd" style="margin-bottom: 20px;">
      <input v-model="form.category_name" placeholder="Category name" />
      <textarea v-model="form.description" placeholder="Description"></textarea>
      <button type="submit">Add</button>
    </form>

    <!-- Hiển thị thông báo -->
    <p v-if="$page.props.flash?.success" style="color: green;">
      {{ $page.props.flash.success }}
    </p>

    <!-- Danh sách category -->
    <table border="1" cellpadding="10">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Description</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="cat in categories" :key="cat.category_id">
          <td>{{ cat.category_id }}</td>

          <!-- Nếu đang sửa -->
          <td v-if="editing === cat.category_id">
            <input v-model="editForm.category_name" />
          </td>
          <td v-else>
            {{ cat.category_name }}
          </td>

          <td v-if="editing === cat.category_id">
            <input v-model="editForm.description" />
          </td>
          <td v-else>
            {{ cat.description }}
          </td>

          <td>
            <!-- Nút sửa -->
            <button v-if="editing !== cat.category_id"
                    @click="startEdit(cat)">
              Edit
            </button>

            <!-- Nút lưu -->
            <button v-if="editing === cat.category_id"
                    @click="submitUpdate(cat.category_id)">
              Save
            </button>

            <!-- Nút huỷ sửa -->
            <button v-if="editing === cat.category_id"
                    @click="cancelEdit">
              Cancel
            </button>

            <!-- Nút xoá -->
            <button @click="deleteCategory(cat.category_id)"
                    style="color: red;">
              Delete
            </button>
          </td>
        </tr>
      </tbody>
    </table>

  </div>
</template>

<script>
import { useForm } from '@inertiajs/vue3'

export default {
  props: {
    categories: Array
  },

  data() {
    return {
      editing: null,
      editForm: {
        category_name: '',
        description: ''
      },
      form: useForm({
        category_name: '',
        description: ''
      })
    }
  },

  methods: {
    submitAdd() {
      this.form.post(this.route('categories.store'), {
        onSuccess: () => this.form.reset()
      })
    },

    startEdit(cat) {
      this.editing = cat.category_id
      this.editForm.category_name = cat.category_name
      this.editForm.description = cat.description
    },

    cancelEdit() {
      this.editing = null
    },

    submitUpdate(id) {
      this.$inertia.put(this.route('categories.update', id), this.editForm, {
        onSuccess: () => {
          this.editing = null
        }
      })
    },

    deleteCategory(id) {
      if (confirm('Delete this category?')) {
        this.$inertia.delete(this.route('categories.destroy', id))
      }
    }
  }
}
</script>
