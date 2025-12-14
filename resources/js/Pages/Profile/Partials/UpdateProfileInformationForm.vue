<script setup>
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
  mustVerifyEmail: {
    type: Boolean,
  },
  status: {
    type: String,
  },
});

const user = usePage().props.auth.user;

const form = useForm({
  name: user.name,
  email: user.email,
});
</script>

<template>
  <section>
    <header>
      <h2 class="text-lg font-medium text-blue-500">
        Thông tin hồ sơ
      </h2>

      <p class="mt-1 text-sm text-blue-400">
        Cập nhật thông tin hồ sơ và địa chỉ email của tài khoản.
      </p>
    </header>

    <form @submit.prevent="form.patch(route('profile.update'))" class="mt-6 space-y-6">
      <div>
        <!-- pass class to InputLabel -->
        <InputLabel for="name" value="Họ và tên" class="text-blue-500" />

        <TextInput
          id="name"
          type="text"
          class="mt-1 block w-full"
          v-model="form.name"
          required
          autofocus
          autocomplete="name"
        />

        <InputError class="mt-2" :message="form.errors.name" />
      </div>

      <div>
        <!-- pass class to InputLabel -->
        <InputLabel for="email" value="Email" class="text-blue-500" />

        <TextInput
          id="email"
          type="email"
          class="mt-1 block w-full"
          v-model="form.email"
          required
          autocomplete="username"
        />

        <InputError class="mt-2" :message="form.errors.email" />
      </div>

      <div v-if="mustVerifyEmail && user.email_verified_at === null">
        <p class="mt-2 text-sm text-blue-500">
          Địa chỉ email của bạn chưa được xác thực.
          <Link
            :href="route('verification.send')"
            method="post"
            as="button"
            class="rounded-md text-sm text-blue-400 underline hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          >
            Nhấn vào đây để gửi lại email xác thực.
          </Link>
        </p>

        <div v-show="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-blue-500">
          Một liên kết xác thực mới đã được gửi tới email của bạn.
        </div>
      </div>

      <div class="flex items-center gap-4">
        <PrimaryButton :disabled="form.processing" class="bg-blue-500 border-blue-500 hover:bg-blue-600">
          Lưu
        </PrimaryButton>

        <Transition
          enter-active-class="transition ease-in-out"
          enter-from-class="opacity-0"
          leave-active-class="transition ease-in-out"
          leave-to-class="opacity-0"
        >
          <p v-if="form.recentlySuccessful" class="text-sm text-blue-400">
            Đã lưu.
          </p>
        </Transition>
      </div>
    </form>
  </section>
</template>

<style>
:root{
  --blue-100: #f0f8ff;
  --blue-200: #e6f7ff;
  --blue-300: #99ccff;
  --blue-400: #4fb0e6;
  --blue-500: #1e88e5;
}

/* global fallback: target the label element inside InputLabel component
   (useful if the component renders a <label> with its own classes) */
.InputLabel-fallback label,
label {
  color: var(--blue-500);
}

/* focus ring for inputs */
input:focus {
  box-shadow: 0 0 0 2px rgba(30,136,229,0.12);
}
</style>
