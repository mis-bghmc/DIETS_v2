<template>
    <Suspense>
      <template #default>
        <slot />
      </template>
      <template #fallback>
        <div class="h-[35vh] flex justify-center items-center">
          <LoaderEgg />
        </div>
      </template>
    </Suspense>
  </template>