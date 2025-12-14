<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
  mustVerifyEmail: {
    type: Boolean,
  },
  status: {
    type: String,
  },
});
</script>

<template>
  <Head title="Profile" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-blue-500">
        Hồ sơ
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 border border-blue-100">
          <UpdateProfileInformationForm
            :must-verify-email="mustVerifyEmail"
            :status="status"
            class="max-w-xl"
          />
        </div>

        <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 border border-blue-100">
          <UpdatePasswordForm class="max-w-xl" />
        </div>

        <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 border border-blue-100">
          <DeleteUserForm class="max-w-xl" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style>
:root{
  --blue-100: #f0f8ff;
  --blue-200: #e6f7ff;
  --blue-300: #99ccff;
  --blue-400: #4fb0e6;
  --blue-500: #1e88e5;
}

/* Optional: small utility to ensure any inner text that used gray is overridden */
.profile-page,
.profile-page h2,
.profile-page .text-sm,
.profile-page .text-base {
  /* no-op placeholder if you want to scope custom CSS later */
}
</style>
