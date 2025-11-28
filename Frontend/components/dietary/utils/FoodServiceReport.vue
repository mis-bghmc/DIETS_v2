<script setup>
import { FoodService } from '~/services/FoodService';

const { formatAllNumeric, formatMonthShort } = useDate();

const props = defineProps({
    date: Date,
    ward: String,
    mealTime: String,
});

const { data, error, status, refresh } = await useLazyAsyncData(
    `served-report-${props.date}-${props.ward}-${props.mealTime}`, 
    () => FoodService.getReport(formatAllNumeric(props.date), props.ward, props.mealTime),
    {
        default: () => [],
        immediate: false
    }
);

const visible = ref(false);


async function show() {
    await refresh();
    
    visible.value = true;
}
</script>

<template>
    <Button text icon="pi pi-external-link" class="!p-0" pt:icon:class="!text-xl" @click="show()" />

    <Dialog 
        modal
        v-model:visible="visible" 
        :dismissableMask=false
        :draggable=false
        pt:root:class="!w-full md:!w-11/12 lg:!w-1/2 !h-full !border-primary !border-2 !rounded-md sm:!text-sm md:!text-base lg:!text-lg" 
        pt:header:class="!pb-2 !pt-3 !border-b !border-primary"
        pt:mask:class="!backdrop-blur-sm" 
    >
        <template #header>
            <div class="flex my-5 gap-2 items-center">
                <span class="font-bold">Food Service Report</span>
            </div>
        </template>

        <ViewTemplate :error="error" :status="status">
            <div class="flex flex-col p-2">
                <div class="flex flex-col">
                    <span class="font-semibold">{{ mealTime }}</span>
                    <span class="font-semibold">{{ formatMonthShort(date) }}</span>
                    <span class="font-semibold">{{ ward }}</span>
                </div>

                <Divider type="dashed" />

                <div class="flex flex-col gap-1">
                    <div class="grid grid-cols-2">
                        <div class="col-span-1">
                            <span>Patients in the ward</span>
                        </div>
                        <div class="col-span-1 text-center">
                            <span>{{ data?.[0]?.total || '-' }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2">
                        <div class="col-span-1">
                            <span>Patients served</span>
                        </div>
                        <div class="col-span-1 text-center">
                            <span>{{ data?.[0]?.served || '-' }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2">
                        <div class="col-span-1">
                            <span>Trays not served</span>
                        </div>
                        <div class="col-span-1 text-center">
                            <span>{{ data?.[0]?.not_served || '-' }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2">
                        <div class="col-span-1">
                            <span>Patients not given food</span>
                        </div>
                        <div class="col-span-1 text-center">
                            <span>{{ data?.[0]?.not_given || '-' }}</span>
                        </div>
                    </div>
                </div>

                <Divider type="dashed" />

                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-muted-color italic">Unlisted Patients</span>
    
                        <div class="px-4">
                            <span>{{ data?.[0]?.unlisted_patients || '-' }}</span>
                        </div>
                    </div>
    
                    <div class="flex flex-col gap-1">
                        <span class="text-muted-color italic">Remarks</span>
    
                        <div class="px-4">
                            <span>{{ data?.[0]?.remarks || '-' }}</span>
                        </div>
                    </div>
                </div>

                <Divider type="dashed" />

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 md:col-span-1 px-4 h-40 flex items-end">
                        <div class="flex flex-col w-full text-center">
                            <span class="font-semibold uppercase">{{ data?.[0]?.server }}</span>
                            <div class="border-t">
                                <span class="text-muted-color italic">Food Server</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-2 md:col-span-1 px-4 h-40 flex items-end">
                        <div class="flex flex-col w-full text-center">
                            <img :src="data?.[0]?.nurse_signature">
                            <span class="font-semibold uppercase">{{ data?.[0]?.nurse }}</span>
                            <div class="w-full border-t">
                                <span class="text-muted-color italic">Nurse</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </ViewTemplate>
    </Dialog>
</template>