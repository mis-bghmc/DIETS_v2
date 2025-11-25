<script setup>
import { PatientsService } from '~/services/PatientsService';

const { data, error, status, refresh } = await useAsyncData(
    'dashboard-patients', 
    () => PatientsService.getAdmitted(),
    {
        default: () => []
    }
);
</script>

<template>
    <ViewTemplate :error="error" :status="status">
        <div class="grid grid-rows-6">
            <div class="row-span-1 flex items-center justify-between">
                <span class="text-muted-color">Patients</span>
                <Button text icon="pi pi-refresh" @click="refresh()" />
            </div>
            <div class="row-span-5 flex items-center justify-center">
                <span class="font-bold text-6xl text-primary">{{ data?.length }}</span>
            </div>
        </div>
    </ViewTemplate>
</template>