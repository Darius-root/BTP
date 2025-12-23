<script setup>
import { ref, nextTick } from 'vue'
import { useForm } from '@inertiajs/vue3'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
  CardDescription,
} from '@/components/ui/card'

import {
  Dialog,
  DialogTrigger,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog'

// --------------------
// State & form
// --------------------
const passwordInput = ref(null)

const form = useForm({
  password: '',
})

// --------------------
// Actions
// --------------------
const deleteUser = () => {
  form.delete(route('profile.destroy'), {
    preserveScroll: true,
    onError: () => {
      passwordInput.value?.focus()
    },
    onFinish: () => {
      form.reset()
    },
  })
}

const onOpenChange = (open) => {
  if (open) {
    nextTick(() => passwordInput.value?.focus())
  } else {
    form.clearErrors()
    form.reset()
  }
}
</script>

<template>
  <section class="max-w-xl mx-auto mt-6">
    <Card>
      <!-- Header -->
      <CardHeader>
        <CardTitle>Delete Account</CardTitle>
        <CardDescription>
          Once your account is deleted, all of its resources and data will be
          permanently deleted. Before deleting your account, please download
          any data or information that you wish to retain.
        </CardDescription>
      </CardHeader>

      <!-- Content -->
      <CardContent>
        <Dialog @openChange="onOpenChange">
          <!-- Trigger -->
          <DialogTrigger as-child>
            <Button variant="destructive">
              Delete Account
            </Button>
          </DialogTrigger>

          <!-- Dialog -->
          <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
              <DialogTitle>Are you absolutely sure?</DialogTitle>
              <DialogDescription>
                This action cannot be undone. Please enter your password to
                confirm account deletion.
              </DialogDescription>
            </DialogHeader>

            <!-- Password -->
            <div class="mt-4 space-y-2">
              <Label for="password" class="sr-only">
                Password
              </Label>

              <Input
                id="password"
                ref="passwordInput"
                type="password"
                v-model="form.password"
                placeholder="Password"
                @keyup.enter="deleteUser"
              />

              <p
                v-if="form.errors.password"
                class="text-sm text-destructive"
              >
                {{ form.errors.password }}
              </p>
            </div>

            <!-- Actions -->
            <DialogFooter class="mt-6 gap-2">
              <Button variant="secondary">
                Cancel
              </Button>

              <Button
                variant="destructive"
                :disabled="form.processing"
                @click="deleteUser"
              >
                Delete Account
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </CardContent>
    </Card>
  </section>
</template>