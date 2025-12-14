<script setup>
import Checkbox from '@/components/Checkbox.vue';
import GuestLayout from '@/layouts/GuestLayout.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
  canResetPassword: {
    type: Boolean,
  },
  status: {
    type: String,
  },
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Đăng nhập" />

    <div v-if="status" class="mb-4 text-sm font-medium text-blue-500">
      {{ status }}
    </div>

    <!-- Social login buttons (plain anchors; not intercepted by Inertia) -->
    <div class="mb-6 space-y-3">
      <div class="flex gap-3">
        <!-- Google -->
        <a
          :href="route('social.redirect', 'google')"
          data-inertia="false"
          class="flex w-full items-center justify-center gap-3 rounded-md border bg-white px-4 py-2 text-sm text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M21.6 12.227c0-.68-.06-1.333-.174-1.96H12v3.72h5.84c-.252 1.36-1.02 2.515-2.176 3.29v2.74h3.52c2.06-1.9 3.24-4.7 3.24-8.09z" fill="#4285F4"/>
            <path d="M12 22c2.97 0 5.46-.98 7.28-2.66l-3.52-2.74c-.98.66-2.24 1.05-3.76 1.05-2.88 0-5.32-1.94-6.19-4.56H1.96v2.86C3.78 19.9 7.64 22 12 22z" fill="#34A853"/>
            <path d="M5.81 13.05A7.01 7.01 0 015.6 12c0-.34.03-.67.07-1.0V8.14H1.96A11.99 11.99 0 000 12c0 1.92.46 3.73 1.28 5.36l4.53-4.31z" fill="#FBBC05"/>
            <path d="M12 6.5c1.62 0 3.08.56 4.23 1.66l3.17-3.17C17.46 2.38 14.97 1.5 12 1.5 7.64 1.5 3.78 3.6 1.96 6.86l4.53 3.64C6.68 8.44 9.12 6.5 12 6.5z" fill="#EA4335"/>
          </svg>
          <span class="font-medium">Đăng nhập bằng Google</span>
        </a>

        <!-- Facebook -->
        <a
          :href="route('social.redirect', 'facebook')"
          data-inertia="false"
          class="flex w-full items-center justify-center gap-3 rounded-md border bg-white px-4 py-2 text-sm text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M22 12.07C22 6.48 17.52 2 11.93 2S2 6.48 2 12.07c0 4.99 3.66 9.12 8.44 9.95v-7.05H8.08v-2.9h2.36V9.41c0-2.33 1.39-3.62 3.52-3.62.99 0 2.03.18 2.03.18v2.23h-1.14c-1.12 0-1.47.7-1.47 1.42v1.7h2.5l-.4 2.9h-2.1v7.05C18.34 21.19 22 17.06 22 12.07z"/>
          </svg>
          <span class="font-medium">Đăng nhập bằng Facebook</span>
        </a>
      </div>

      <div class="flex items-center justify-center">
        <span class="text-sm text-blue-400">hoặc</span>
      </div>
    </div>

    <!-- Email / password form -->
    <form @submit.prevent="submit">
      <div>
        <InputLabel for="email" value="Email"  class="text-blue-500"/>

        <TextInput
          id="email"
          type="email"
          class="mt-1 block w-full"
          v-model="form.email"
          required
          autofocus
          autocomplete="username"
        />

        <InputError class="mt-2 text-blue-500" :message="form.errors.email" />
      </div>

      <div class="mt-4">
        <InputLabel for="password" value="Mật khẩu" class="text-blue-500" />

        <TextInput
          id="password"
          type="password"
          class="mt-1 block w-full"
          v-model="form.password"
          required
          autocomplete="current-password"
        />

        <InputError class="mt-2 text-blue-500" :message="form.errors.password" />
      </div>

      <div class="mt-4 block">
        <label class="flex items-center">
          <Checkbox name="remember" v-model:checked="form.remember" />
          <span class="ms-2 text-sm text-blue-400">Ghi nhớ đăng nhập</span>
        </label>
      </div>

      <div class="mt-4 flex items-center justify-end">
        <a
          v-if="canResetPassword"
          :href="route('password.request')"
          class="rounded-md text-sm text-blue-400 underline hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          data-inertia="false"
        >
          Quên mật khẩu?
        </a>

        <PrimaryButton
          class="ms-4 bg-blue-500 border-blue-500 hover:bg-blue-600 focus:ring-blue-500"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Đăng nhập
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

/* Fallbacks if components don't forward class props */
label { color: var(--blue-500); }
.input-error, .input-error * { color: var(--blue-500); }

/* Primary button fallback */
button.bg-blue-500,
button.bg-blue-500:hover {
  background-color: var(--blue-500);
  border-color: var(--blue-500);
  color: #fff;
}

/* Input focus ring using the blue palette */
input:focus,
textarea:focus,
select:focus {
  box-shadow: 0 0 0 4px rgba(30,136,255,0.08);
  border-color: var(--blue-500);
}
</style>
