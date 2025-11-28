<script setup>
const patients_store = usePatientsStore();
const { admitted_patients } = storeToRefs(patients_store);
</script>

<template>
    <div class="grid grid-rows-6">
        <div class="row-span-1 flex items-center justify-between">
            <span class="text-muted-color">Patients</span>
    
            <SearchPatients />
        </div>
        <div class="row-span-5 flex items-center justify-center">
            <ClientOnly>
                <span class="font-bold text-6xl text-primary">{{ admitted_patients?.length }}</span>
            </ClientOnly>
        </div>
    </div>
</template>