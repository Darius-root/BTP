<script setup>
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'

defineProps({
  mustVerifyEmail: Boolean,
  status: String,
})

const user = usePage().props.auth.user

const form = useForm({
  name: user.name,
  email: user.email,
})
</script>
<template>
  <section class="max-w-xl mx-auto mt-6">
    <Card>
      <!-- Card Header -->
      <CardHeader>
        <CardTitle>Profile Information</CardTitle>
        <CardDescription>
          Update your account's profile information and email address.
        </CardDescription>
      </CardHeader>

      <!-- Card Content (formulaire) -->
      <CardContent>
        <form @submit.prevent="form.patch(route('profile.update'))" class="space-y-6">
          
          <!-- Name -->
          <div>
            <Label for="name">Name</Label>
            <Input
              id="name"
              type="text"
              v-model="form.name"
              required
              autofocus
              autocomplete="name"
              class="mt-1 block w-full"
            />
            <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <!-- Email -->
          <div>
            <Label for="email">Email</Label>
            <Input
              id="email"
              type="email"
              v-model="form.email"
              required
              autocomplete="username"
              class="mt-1 block w-full"
            />
            <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
          </div>

          <!-- Email verification -->
          <div v-if="mustVerifyEmail && user.email_verified_at === null">
            <p class="mt-2 text-sm text-gray-800">
              Your email address is unverified.
              <Link
                :href="route('verification.send')"
                method="post"
                as="button"
                class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
              >
                Click here to re-send the verification email.
              </Link>
            </p>
            <div v-show="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
              A new verification link has been sent to your email address.
            </div>
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


