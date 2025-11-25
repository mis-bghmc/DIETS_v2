<script setup>
const { patient } = useRoute().params;
const enccode = decodeURIComponent(patient || '');
</script>

<template>
    <Patient :enccode="enccode" />
</template>