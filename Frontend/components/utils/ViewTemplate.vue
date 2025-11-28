<script setup>
const props = defineProps({
    status: String,
    error: Error | Object
});
</script>

<template>
    <div class="print:m-0">
        <div v-if="error">
            <span class="font-bold text-red-400">An error has occured. Please log it into the intranet or call extension 202.</span>
        </div>
        <div v-else-if="status === 'pending'">
            <div class="h-[35vh] flex justify-center items-center">
                <LoaderEgg />
            </div>
        </div>
        <div v-else>
            <slot />
        </div>
    </div>
</template>