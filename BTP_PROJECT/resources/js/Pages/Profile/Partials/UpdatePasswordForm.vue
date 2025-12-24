<script setup>
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const passwordInput = ref(null)
const currentPasswordInput = ref(null)

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation')
                passwordInput.value.focus()
            }
            if (form.errors.current_password) {
                form.reset('current_password')
                currentPasswordInput.value.focus()
            }
        },
    })
}
</script>
<template>
  <section class="max-w-xl mx-auto mt-6">
    <Card>
      <!-- Card Header -->
      <CardHeader>
        <CardTitle>Update Password</CardTitle>
        <CardDescription>
          Ensure your account is using a long, random password to stay secure.
        </CardDescription>
      </CardHeader>

      <!-- Card Content (formulaire) -->
      <CardContent>
        <form @submit.prevent="updatePassword" class="space-y-6">

          <!-- Current Password -->
          <div>
            <Label for="current_password">Current Password</Label>
            <Input
              id="current_password"
              type="password"
              ref="currentPasswordInput"
              v-model="form.current_password"
              class="mt-1 block w-full"
              autocomplete="current-password"
            />
            <p v-if="form.errors.current_password" class="mt-2 text-sm text-red-600">
              {{ form.errors.current_password }}
            </p>
          </div>

          <!-- New Password -->
          <div>
            <Label for="password">New Password</Label>
            <Input
              id="password"
              type="password"
              ref="passwordInput"
              v-model="form.password"
              class="mt-1 block w-full"
              autocomplete="new-password"
            />
            <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
              {{ form.errors.password }}
            </p>
          </div>

          <!-- Confirm Password -->
          <div>
            <Label for="password_confirmation">Confirm Password</Label>
            <Input
              id="password_confirmation"
              type="password"
              v-model="form.password_confirmation"
              class="mt-1 block w-full"
              autocomplete="new-password"
            />
            <p v-if="form.errors.password_confirmation" class="mt-2 text-sm text-red-600">
              {{ form.errors.password_confirmation }}
            </p>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-4">
            <Button type="submit" :disabled="form.processing">Save</Button>
            <Transition
              enter-active-class="transition ease-in-out"
              enter-from-class="opacity-0"
              leave-active-class="transition ease-in-out"
              leave-to-class="opacity-0"
            >
              <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
            </Transition>
          </div>
        </form>
      </CardContent>
    </Card>
  </section>
</template>