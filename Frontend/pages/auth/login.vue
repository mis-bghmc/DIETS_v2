<script setup>
import { AuthService } from "~/services/AuthService";
import DIETS_logo from '~/public/images/DIETS_logo.svg';

definePageMeta({
    layout: false,
});

const toast = useToast();
const route = useRoute();

const username = ref('');
const password = ref('');

const invalid_username = ref(false);

const loading = ref(false);
const loggedin = ref(false);

//  Login
async function login() {
    if(loading.value || loggedin.value) return;

    loading.value = true;
    invalid_username.value = false;

    try{
        if(!username.value || username.value.trim() === '') {
            invalid_username.value = true;
            return;
        }

        await AuthService.login({username: username.value, password: password.value});

        loggedin.value = true;

        navigateTo('/');

    }catch(error) {
        const detail = [422, 403].includes(error.status) ? 'Invalid login credentials.' : 'An error has occured. Please log it into the intranet or call extension 202. [Login]';
        
        toast.add({ severity: 'error', summary: 'Error!', detail: detail, life: 5000 });

    }finally {
        loading.value = false;
    }
}

//  
watch(
    () => route.query.status,
    async () => {
        if(route.query.status) {
            const detail = route.query.status === '403' 
                ? 'Sorry, but you do not have permission to access the system.'
                : 'An error has occured. Please log it into the intranet or call extension 202. [Login]';
            
            loggedin.value = false;
            toast.add({ severity: 'error', summary: 'Error!', detail: detail, life: 5000 });
            navigateTo('/auth/login');
        }
    },
    { immediate: true }
);
</script>

<template>
    <Toast />
    <div class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen min-w-[100vw] overflow-hidden">
        <div class="flex flex-col items-center justify-center">
            <div style="border-radius: 56px; padding: 0.3rem; background: linear-gradient(180deg, var(--primary-color) 10%, rgba(33, 150, 243, 0) 30%)">
                <div class="w-full bg-surface-0 dark:bg-surface-900 py-20 px-8 sm:px-20" style="border-radius: 53px">
                    <div class="flex flex-col gap-2 items-center mb-8">
                        <DIETS_logo class="text-primary w-60 h-60"/>
                        <div class="w-3/4 flex justify-between">
                            <span class="text-surface-900 dark:text-surface-0 text-3xl font-medium">D</span>
                            <span class="text-surface-900 dark:text-surface-0 text-3xl font-medium">I</span>
                            <span class="text-surface-900 dark:text-surface-0 text-3xl font-medium">E</span>
                            <span class="text-surface-900 dark:text-surface-0 text-3xl font-medium">T</span>
                            <span class="text-surface-900 dark:text-surface-0 text-3xl font-medium">S</span>
                        </div>
                        <span class="text-muted-color font-medium text-center">Dietary Information and Engagement Technology System</span>
                    </div>

                    <div class="flex flex-col gap-2" @keydown.enter="login">
                        <FloatLabel variant="on">
                            <InputText v-model="username" id="username" type="text" class="w-full" :invalid="invalid_username" />
                            <label for="username">Username</label>
                        </FloatLabel>

                        <FloatLabel variant="on">
                            <Password v-model="password" id="password" class="w-full" :toggleMask="true" fluid :feedback="false" />
                            <label for="password">Password</label>
                        </FloatLabel>

                        <Button class="w-full" :label="loading ? 'Logging in...' : 'Login'" :loading="loading" :disabled="loggedin || loading" @click="login" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
