<script setup>
import NotFound from '~/public/images/404.svg'
import ServerError from '~/public/images/500.svg'
import ServiceUnavailable from '~/public/images/503.svg'

defineProps({
    error: Object
})

const errors = {
    404: NotFound,
    500: ServerError,
    503: ServiceUnavailable
};
</script>

<template>
    <div class="flex flex-col items-center h-screen py-20">
        <component :is="errors[error?.statusCode] || ServerError" class="text-primary h-screen w-screen" />
        <p class="mt-4">{{ error?.message }}</p>
        <Button label="Go back home" as="router-link" to="/" severity="secondary" />
    </div>
</template>
