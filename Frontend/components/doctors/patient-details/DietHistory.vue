<script setup>
import { DoctorsOrdersService } from '~/services/DoctorsOrdersService';
import NoData from '~/public/images/no-data.svg';

const { formatDateTime } = useDate();

const props = defineProps({
    data: Object
});

const emit = defineEmits(['updated']);

const patient = props.data?.[0];

const { data : history, error, status, refresh } = await useAsyncData(
    `doctors-orders-history-${patient.enccode}`, 
    () => DoctorsOrdersService.getHistory(patient?.hpercode),
    {
        default: () => []
    }
);

const diet_details = ref();
const visible = ref(false);

const order_status = ref({
    A: {
        label: 'Active',
        severity: 'success'
    },
    I: {
        label: 'Inactive',
        severity: 'danger'
    },
    P: {
        label: 'Pending',
        severity: 'warn'
    }
})


//  Show patient diet details
function openDietDetails(event) {
    diet_details.value = event.data;
    visible.value = true;
}


//  Watcher for doctors orders history
watch(
    history,
    (new_value) => {
        emit('updated', new_value);
    },
    { immediate: true }
)


//  Exposed function
defineExpose({
    refresh
});
</script>

<template>
    <div>
        <div>
            <h5 class="flex gap-2 items-center"> 
                <Icon name="fluent:clipboard-task-list-16-filled" size="1.5em" class="text-primary"/> 
                <span class="font-bold flex-1">Diet History</span>
                <Button text class="p-0" @click="refresh()"> 
                    <Icon name="fluent:arrow-clockwise-12-filled" size="2rem" class="icon" />
                </Button>
            </h5>
        </div>

        <ViewTemplate :error="error" :status="status">
            <DataTable 
                :value="history" 
                datakey="docointkey"
                stripedRows 
                :rowHover="true" 
                paginator
                :rows="5" 
                :rowsPerPageOptions="[5, 10, 20, 50]"
                pt:root:class="sm:text-xs md:text-xs lg:text-base hover:cursor-pointer"
                @row-click="openDietDetails"
            >
                <template #empty>
                    <div class="container mx-auto flex flex-col justify-center items-center py-4 h-[50vh]">
                        <NoData class="text-primary" />
                        <p class="text-lg font-bold mt-4">No data found.</p>
                    </div>
                </template>

                <Column field="dodate" header="Date" style="width: 25%">
                    <template #body="{data}">
                        <span>{{ formatDateTime(data?.dodate) }}</span>
                    </template>
                </Column>

                <Column field="dietname" header="Diet" style="width: 25%"></Column>

                <Column header="Entry by" style="width: 25%">
                    <template #body="{data}">
                        <span>{{ data?.lname ? `${data?.lname}, ${data?.fname}` : '-' }}</span>
                    </template>
                </Column>

                <Column header="Status" style="width: 25%">
                    <template #body="{data}">
                        <div class="w-full">
                            <Tag :severity="order_status[data?.dostatus].severity" class="w-full text-base font-bold">{{ order_status[data?.dostatus].label }}</Tag>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </ViewTemplate>

        <Dialog 
            modal
            v-model:visible="visible" 
            :dismissableMask=true
            :draggable=false
            pt:root:class="!w-full md:!w-11/12 lg:!w-1/2 !border-primary !p-2 !border !rounded-md sm:!text-sm md:!text-base lg:!text-lg" 
            pt:header:class="!p-4"
            pt:mask:class="!backdrop-blur-sm" 
        >
            <template #header>
                <div class="flex gap-2 items-center">
                    <Icon name="fluent:food-16-filled" size="1.5em" class="text-primary"/> 
                    <span class="font-bold flex-1">Diet Details</span>
                </div>
            </template>

            <DietDetails :data="diet_details" :from-history="true" :order-status="order_status[diet_details?.dostatus]" />
        </Dialog>
    </div>
</template>