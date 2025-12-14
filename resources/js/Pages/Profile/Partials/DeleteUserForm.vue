<script setup>
import DangerButton from '@/components/DangerButton.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import Modal from '@/components/Modal.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
  password: '',
});

const confirmUserDeletion = () => {
  confirmingUserDeletion.value = true;

  nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
  form.delete(route('profile.destroy'), {
    preserveScroll: true,
    onSuccess: () => closeModal(),
    onError: () => passwordInput.value.focus(),
    onFinish: () => form.reset(),
  });
};

const closeModal = () => {
  confirmingUserDeletion.value = false;

  form.clearErrors();
  form.reset();
};
</script>

<template>
  <section class="space-y-6">
    <header>
      <h2 class="text-lg font-medium text-blue-500">
        Xóa tài khoản
      </h2>

      <p class="mt-1 text-sm text-blue-400">
        Khi bạn xóa tài khoản, tất cả dữ liệu và tài nguyên liên quan sẽ bị xóa vĩnh viễn. Trước khi xóa, hãy tải xuống mọi dữ liệu hoặc thông tin bạn muốn lưu giữ.
      </p>
    </header>

    <!-- Keep this primary action red -->
    <DangerButton
      @click="confirmUserDeletion"
      class="bg-red-600 border-red-600 hover:bg-red-700 focus:ring-red-500"
    >
      Xóa tài khoản
    </DangerButton>

    <Modal :show="confirmingUserDeletion" @close="closeModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-blue-500">
          Bạn có chắc muốn xóa tài khoản không?
        </h2>

        <p class="mt-1 text-sm text-blue-400">
          Khi tài khoản bị xóa, mọi dữ liệu và tài nguyên liên quan sẽ bị xóa vĩnh viễn. Vui lòng nhập mật khẩu để xác nhận bạn muốn xóa tài khoản.
        </p>

        <div class="mt-6">
          <InputLabel for="password" value="Mật khẩu" class="sr-only" />

          <TextInput
            id="password"
            ref="passwordInput"
            v-model="form.password"
            type="password"
            class="mt-1 block w-3/4"
            placeholder="Mật khẩu"
            @keyup.enter="deleteUser"
          />

          <InputError :message="form.errors.password" class="mt-2 text-blue-500" />
        </div>

        <div class="mt-6 flex justify-end">
          <SecondaryButton
            @click="closeModal"
            class="text-blue-500 border border-blue-100 hover:bg-blue-50"
          >
            Hủy
          </SecondaryButton>

          <!-- Keep the modal's DangerButton red as well -->
          <DangerButton
            class="ms-3 bg-red-600 border-red-600 hover:bg-red-700"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
            @click="deleteUser"
          >
            Xóa tài khoản
          </DangerButton>
        </div>
      </div>
    </Modal>
  </section>
</template>

<style>
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

  --red-600: #dc2626; /* Tailwind red-600 */
  --red-700: #b91c1c; /* Tailwind red-700 */
}

/* Ensure any label text falls back to blue if component doesn't forward classes */
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

/* InputError fallback color */
.input-error,
.input-error * {
  color: var(--blue-500);
}

/* Generic blue button fallback (used for SecondaryButton etc.) */
button.bg-blue-500,
button.bg-blue-500:hover {
  background-color: var(--blue-500);
  border-color: var(--blue-500);
  color: #fff;
}

/* Danger button fallback — keep red */
button.bg-red-600,
button.bg-red-600:hover {
  background-color: var(--red-600);
  border-color: var(--red-600);
  color: #fff;
}

/* Secondary button fallback */
button.text-blue-500 {
  color: var(--blue-500);
}
</style>
