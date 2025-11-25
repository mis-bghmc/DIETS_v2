<script setup>
import { FoodRequestsService } from '~/services/FoodRequestsService';
import NoData from '~/public/images/no-data.svg';

const echo = useEcho();
const toast = useToast();

const user_store = useUserStore();
const { user } = storeToRefs(user_store);

const { formatMonthShort, formatTime, formatAllNumeric, formatMonthYear } = useDate();

const props = defineProps({
    date: Date,
    month: Number,
    year: Number
});

const emit = defineEmits(['update']);

const { data, error, status, refresh } = await useAsyncData(
    'food-requests', 
    () => FoodRequestsService.getRequests(formatMonthYear(props.year, props.month)),
    {
        default: () => []
    }
);

const requests = ref();
const requests_dates = ref([]);
const requests_grouped = ref();

const options_status = ['ALL', 'CREATED', 'DONE', 'NOT DONE'];
const options_meals = ['ALL', 'BREAKFAST', 'LUNCH', 'DINNER', 'AM SNACK', 'PM SNACK', 'MN SNACK'];

const filters = ref({
    Status: options_status, 
    Meal: options_meals
});

const filter_object = ref({
    Status: 'ALL',
    Meal: 'ALL'
});

const visible_remarks = ref(false);
const to_be_updated = ref();
const to_be_status = ref();
const update_remarks = ref();

const expand = ref(false);
const saving = ref(false);


// Update request status
async function updateStatus() {
    saving.value = true;

    try{
        await FoodRequestsService.update({
            headers: {
                'X-Socket-Id' : echo.socketId()
            },
            body: {
                id: to_be_updated.value?.id,
                status: to_be_status.value,
                update_remarks: update_remarks.value,
                updated_by: user.value.employeeid
            }
        });
        
        visible_remarks.value = false;

        await refresh();

    }catch(error){
        toast.add({ severity: 'error', summary: 'Error!', detail: 'An error has occured. Please log it into the intranet or call extension 202. [Food Requests -> update]' });

    }finally{
        saving.value = false;
    }
}

//  Open remarks
function openRemarks(request, status) {
    to_be_updated.value = request;
    to_be_status.value = status;
    visible_remarks.value = true;
}

//  Close remarks
function clearToBeUpdated() {
    to_be_updated.value = null;
    to_be_status.value = null;
    update_remarks.value = null;
}

//  Check if request is a snack
function isSnack(request) {
    return ['AM SNACK', 'PM SNACK', 'MN SNACK'].includes(request?.meal);
}

//  Filter notifications
function filterRequests() {
    const filtered = data.value?.filter(filterValidation);
    requests.value = filtered;
}

//  Callback function for filtering notifications
function filterValidation(request) {
    const { Status, Meal } = filter_object.value;
    const status_match = Status === 'ALL' || Status === request?.status;
    const meal_match = Meal === 'ALL' || Meal === request?.meal;
    
    return status_match && meal_match;
};

//  Clear notification filter
function clearFilter() {
    filter_object.value = {
        Status: 'ALL',
        Meal: 'ALL'
    };
}

//  Set date selected requets
function setRequests() {
    requests.value = requests_grouped.value?.[formatAllNumeric(props.date)];
}


//  Watcher for data
 watch(
    data, 
    (new_value) => {
        requests_grouped.value = Object.groupBy(new_value, ({request_date}) => request_date);
        requests_dates.value = Object.keys(requests_grouped.value)?.map(key => new Date(key).getDate());
        
        clearFilter();
        setRequests();
        
        emit('update', requests_dates.value);
    },
    {
        immediate: true
    }
);


//  On mounted
onMounted(()=> {
    echo.channel('food-requests-channel')    
        .listen('.food-requests', async (e) => {
            if(props.month === new Date(e.message)?.getMonth()) await refresh();
        })
})
</script>

<template>
    <Dialog
        modal 
        v-model:visible="visible_remarks" 
        class="!w-full md:!w-1/2 lg:!w-5/12"
        pt:root:class="!rounded-md !border-2 !border-primary sm:!text-sm md:!text-base lg:!text-lg"
        pt:header:class="!pb-2 !pt-3 !rounded-t-lg !border-b !border-primary"
        pt:mask:class="!backdrop-blur-sm" 
        @after-hide="clearToBeUpdated()"
    >
        <template #header>
            <span>Remarks</span>
        </template>

        <div class="w-full pt-4">
            <Textarea v-model="update_remarks" :invalid="!update_remarks && to_be_status === 'NOT DONE'" class="w-full" />
        </div>

        <Button label="Submit" :disabled="(!update_remarks && to_be_status === 'NOT DONE') || saving" :loading="saving" class="w-full" @click="updateStatus()" />
    </Dialog>

    <div class="sm:text-sm md:text-base lg:text-lg">
        <div class="h-10 p-2 border-b border-primary flex items-center">
            <div class="flex-1">
                <Button text :icon="expand ? 'pi pi-minus' : 'pi pi-plus'" :disabled="!requests?.length" @click="expand = !expand" />
                <span class="font-bold">Food Requests</span>
            </div>
            <div class="mb-1">
                <FoodRequestsForm :date="date" @created="refresh" />
            </div>
        </div>
        <div class="p-2" @click="expand = true">
            <div>
                <div class="flex items-center gap-2 p-2">
                    <div class="flex flex-wrap flex-1 justify-left">
                        <span class="font-semibold">Total: </span>
                        <span>{{ requests?.length || 0 }}</span>
                    </div>
            
                    <div class="flex flex-wrap gap-2">
                        <template v-for="(value, key) in filters">
                            <FloatLabel class="w-auto" variant="on">
                                <Select class="w-full" :id="key" :options="value" v-model="filter_object[key]" @change="filterRequests()" />
                                <label :for="key">{{ key }}</label>
                            </FloatLabel>
                        </template>
                    </div>
                </div>

                <Divider type="dashed" />
            </div>

            <ViewTemplate :error="error" :status="status">
                <div v-if="!requests?.length" class="container mx-auto flex flex-col justify-center items-center py-4 h-[19vh]">
                    <NoData class="text-primary" />
                    <p class="text-lg font-bold mt-4">No data found.</p>
                </div>

                <div v-else class="flex flex-col gap-4" :class="['h-[12vh] overflow-hidden', {'h-max overflow-visible': expand}]">
                    <div v-for="request in requests">
                        <div class="pr-2">
                            <div class="grid grid-cols-12">
                                <div class="col-span-1 flex items-center justify-center">
                                    <Icon v-if="isSnack(request)" name="game-icons:hamburger" class="text-4xl text-orange-600" />
                                    <Icon v-else name="game-icons:meat" class="text-4xl text-red-600" />
                                </div>
            
                                <div class="col-span-11 flex flex-wrap"> 
                                    <div class="flex flex-col w-full">
                                        <div class="font-bold">{{ request?.requesting }}</div>

                                        <div>
                                            <span>Requesting </span>
                                            <span class="text-red-400 font-semibold underline">{{ request?.qnty }}</span>
                                            <span> meal(s) for </span>
                                            <span class="text-emerald-400 font-semibold underline">{{ request?.meal }}</span>
                                        </div>

                                        <div class="italic text-sm mb-1">
                                            <div v-if="request?.remarks">
                                                <span>&quot</span>
                                                <span>{{ request?.remarks }}</span>
                                                <span>&quot</span>
                                            </div>
                                        </div>

                                        <div class="text-sm">
                                            <div v-if="request?.status === 'CREATED'" class="flex flex-wrap items-end">
                                                <div class="flex-1">
                                                    <span>Created by </span>
                                                    <span class="font-bold">{{ request?.firstname }} {{ request?.lastname }}</span>
                                                    <span> at </span>
                                                    <span class="font-bold">{{ formatTime(request?.created_at) }}</span>
                                                </div>
                                                <div class="flex gap-1">
                                                    <Button pt:root:class="!min-w-20 !p-0 !bg-emerald-400 !border-emerald-500" icon="pi pi-check" :loading="false" @click="openRemarks(request, 'DONE')" />
                                                    <Button pt:root:class="!min-w-20 !p-0 !bg-red-400 !border-red-500" icon="pi pi-times" :loading="false" @click="openRemarks(request, 'NOT DONE')" />
                                                </div>
                                            </div>

                                            <div v-else class="flex flex-col text-sm">
                                                <div v-if="request?.status !== 'CREATED'">
                                                    <span>Marked as </span>
                                                    <span class="font-bold" :class="{'text-emerald-400': request?.status === 'DONE', 'text-red-400': request?.status === 'NOT DONE'}">{{ request?.status }}</span>
                                                    <span> by </span>
                                                    <span class="font-bold">{{ request?.firstname }} {{ request?.lastname }}</span>
                                                    <span> on </span>
                                                    <span class="italic font-bold">{{ formatMonthShort(request?.updated_at) }} {{ formatTime(request?.updated_at) }}</span>
                                                    <span v-if="request?.update_remarks"> | </span>
                                                    <span>{{ request?.update_remarks }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
        
                        <Divider type="dashed" />
                    </div>
                </div>
            </ViewTemplate>
        </div>
    </div>
</template>