<script setup>
import GuestLayout from '@/layouts/GuestLayout.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
  status: {
    type: String,
  },
});

const form = useForm({
  email: '',
});

const submit = () => {
  form.post(route('password.email'));
};
</script>

<template>
  <GuestLayout>
    <Head title="Quên mật khẩu" />

    <div class="mb-4 text-sm text-pink-400">
      Quên mật khẩu? Không sao cả. Hãy cho chúng tôi biết địa chỉ email của bạn
      và chúng tôi sẽ gửi liên kết đặt lại mật khẩu để bạn có thể chọn mật khẩu mới.
    </div>

    <div v-if="status" class="mb-4 text-sm font-medium text-pink-500">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <div>
        <InputLabel for="email" value="Email" class="text-pink-500" />

        <TextInput
          id="email"
          type="email"
          class="mt-1 block w-full"
          v-model="form.email"
          required
          autofocus
          autocomplete="username"
        />

        <InputError class="mt-2 text-pink-500" :message="form.errors.email" />
      </div>

      <div class="mt-4 flex items-center justify-end">
        <PrimaryButton
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          class="bg-pink-500 border-pink-500 hover:bg-pink-600 focus:ring-pink-500"
        >
          Gửi liên kết đặt lại mật khẩu
        </PrimaryButton>
      </div>
    </form>
  </GuestLayout>
</template>

<style>
:root{
  --blue-100: #f0f7ff;
  --blue-200: #e6f4ff;
  --blue-300: #70c1ff;
  --blue-400: #4da6ff;
  --blue-500: #1e88ff;
}

/* Dự phòng nếu các component không truyền class props */
label { color: var(--blue-500); }
.input-error, .input-error * { color: var(--blue-500); }
button.bg-blue-500, button.bg-blue-500:hover {
  background-color: var(--blue-500);
  border-color: var(--blue-500);
  color: #fff;
}
input:focus, textarea:focus, select:focus {
  box-shadow: 0 0 0 4px rgba(30,136,255,0.08);
  border-color: var(--blue-500);
}
</style>

