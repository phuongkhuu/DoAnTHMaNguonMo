<script setup>
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const updatePassword = () => {
  form.put(route('password.update'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
    onError: () => {
      if (form.errors.password) {
        form.reset('password', 'password_confirmation');
        passwordInput.value.focus();
      }
      if (form.errors.current_password) {
        form.reset('current_password');
        currentPasswordInput.value.focus();
      }
    },
  });
};
</script>

<template>
  <section>
    <header>
      <h2 class="text-lg font-medium text-blue-500">
        Cập nhật mật khẩu
      </h2>

      <p class="mt-1 text-sm text-blue-400">
        Hãy đảm bảo tài khoản của bạn sử dụng mật khẩu dài và ngẫu nhiên để giữ an toàn.
      </p>
    </header>

    <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
      <div>
        <InputLabel for="current_password" value="Mật khẩu hiện tại" class="text-blue-500" />

        <TextInput
          id="current_password"
          ref="currentPasswordInput"
          v-model="form.current_password"
          type="password"
          class="mt-1 block w-full"
          autocomplete="current-password"
        />

        <InputError :message="form.errors.current_password" class="mt-2 text-blue-500" />
      </div>

      <div>
        <InputLabel for="password" value="Mật khẩu mới" class="text-blue-500" />

        <TextInput
          id="password"
          ref="passwordInput"
          v-model="form.password"
          type="password"
          class="mt-1 block w-full"
          autocomplete="new-password"
        />

        <InputError :message="form.errors.password" class="mt-2 text-blue-500" />
      </div>

      <div>
        <InputLabel for="password_confirmation" value="Xác nhận mật khẩu" class="text-blue-500" />

        <TextInput
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          class="mt-1 block w-full"
          autocomplete="new-password"
        />

        <InputError :message="form.errors.password_confirmation" class="mt-2 text-blue-500" />
      </div>

      <div class="flex items-center gap-4">
        <PrimaryButton
          :disabled="form.processing"
          class="bg-blue-500 border-blue-500 hover:bg-blue-600 focus:ring-blue-500"
        >
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

/* Ensure labels rendered by InputLabel become blue when class forwarding isn't available */
label {
  color: var(--blue-500);
}

/* Input focus ring using the blue palette */
input:focus,
textarea:focus,
select:focus {
  box-shadow: 0 0 0 4px rgba(30,136,229,0.08);
  border-color: var(--blue-500);
}

/* PrimaryButton fallback styling if the component doesn't accept class props */
button.bg-blue-500,
button.bg-blue-500:hover {
  background-color: var(--blue-500);
  border-color: var(--blue-500);
  color: #fff;
}

/* InputError text color fallback */
.input-error,
.input-error * {
  color: var(--blue-500);
}
</style>
