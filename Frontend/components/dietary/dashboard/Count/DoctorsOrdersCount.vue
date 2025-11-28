<script setup>
import { DoctorsOrdersService } from '~/services/DoctorsOrdersService';

const echo = useEcho();

const { data: patients, error, status, refresh } = await useAsyncData(
    'dashboard-orders', 
    () => DoctorsOrdersService.getDoctorsOrders(),
    {
        default: () => []
    }
);

const orders = ref(0);


//  Watcher for patients
watch(patients, (new_value) => { orders.value = new_value?.filter(item => item.has_orders === 'Y')?.length }, { immediate: true });


// On mounted 
onMounted(() => {
    echo.channel('notification-channel')    
        .listen('.new-doctors-order', () => {
            refresh();
        })
});
</script>

<template>
    <ViewTemplate :error="error" :status="status">
        <div class="grid grid-rows-6">
            <div class="row-span-1 flex items-center justify-between">
                <span class="text-muted-color">Doctors' Orders</span>
                <DoctorsOrders :patients="patients" />
            </div>
            <div class="row-span-5 flex items-center justify-center">
                <span class="font-bold text-6xl text-primary">{{ orders }}</span>
            </div>
        </div>
    </ViewTemplate>
</template>