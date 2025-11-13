<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';

interface DevUser {
    email: string;
    label: string;
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const props = defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
    isDev?: boolean;
    devUsers?: DevUser[];
}>();

const handleDevLogin = (email: string) => {
    // Create a form and submit it to the login link endpoint
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/laravel-login-link-login';

    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '';
    form.appendChild(csrfInput);

    // Add email
    const emailInput = document.createElement('input');
    emailInput.type = 'hidden';
    emailInput.name = 'email';
    emailInput.value = email;
    form.appendChild(emailInput);

    // Submit form
    document.body.appendChild(form);
    form.submit();
};
</script>

<template>
    <AuthBase
        title="Log in to your account"
        description="Enter your email and password below to log in"
    >
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@example.com"
                    />
                    <InputError
                        :message="errors.email"
                        help-text="Enter a valid email address like user@example.com"
                    />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm"
                            :tabindex="5"
                        >
                            Forgot password?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Password"
                    />
                    <InputError
                        :message="errors.password"
                        help-text="Must be at least 8 characters with letters and numbers"
                    />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Remember me</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    Log in
                </Button>
            </div>

            <div
                class="text-center text-sm text-muted-foreground"
                v-if="canRegister"
            >
                Don't have an account?
                <TextLink :href="register()" :tabindex="5">Sign up</TextLink>
            </div>
        </Form>

        <div v-if="isDev && devUsers && devUsers.length > 0" class="mt-8">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <span class="w-full border-t" />
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-background px-2 text-muted-foreground">
                        Development Only
                    </span>
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <div
                    class="rounded-md border border-yellow-500/50 bg-yellow-50 p-3 dark:bg-yellow-950/20"
                >
                    <p class="text-xs text-yellow-800 dark:text-yellow-200">
                        Quick login for development testing. These buttons only
                        appear in local environment.
                    </p>
                </div>

                <div class="space-y-2">
                    <Button
                        v-for="user in devUsers"
                        :key="user.email"
                        type="button"
                        variant="outline"
                        class="w-full justify-start text-left"
                        @click="handleDevLogin(user.email)"
                    >
                        <div class="flex w-full flex-col">
                            <span class="font-medium">{{ user.email }}</span>
                            <span class="text-xs text-muted-foreground">{{
                                user.label
                            }}</span>
                        </div>
                    </Button>
                </div>
            </div>
        </div>
    </AuthBase>
</template>
