<script setup>
useHead({
  bodyAttrs: {
    class: 'bg-white text-black',
  },
})
</script>

<template>
  <div>
    <slot />
  </div>
  <div class="fixed bottom-12 right-10 z-10 print:hidden">
    <button class="h-24 w-24 border-4 border-primary rounded-full bg-primary-50 shadow-2xl" onclick="window.print()">
      <i class="pi pi-print !text-5xl text-primary"></i>
    </button>
  </div>
</template>

<style scoped>
@page {
  background: white;
}
</style>