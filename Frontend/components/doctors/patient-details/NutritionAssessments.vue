<script setup>
import { NutritionService } from '~/services/NutritionService';
import NoData from '~/public/images/no-data.svg';

const { formatDateTime } = useDate();

const user_store = useUserStore();
const { user } = storeToRefs(user_store);

const props = defineProps({
    data: Object
});

const patient = ref(props.data?.[0]);

const { data: history, error, status, refresh } = await useAsyncData(
    'nutrition-assessment-history', 
    () => NutritionService.getAssessmentHistory(patient.value?.enccode),
    {
        default: () => []
    }
);

const selected = ref();


//  Update screening
function update() {
    selected.value = null;
    
    nextTick(async () => {
        await refresh();
    });
}
</script>

<template>
    <ViewTemplate :error="error" :status="status">
        <div class="grid grid-cols-6 gap-4">
            <div class="col-span-6 md:col-span-2" :class="{'hidden md:block': selected}">
                <div v-if="user.user_level === '59'">
                    <NutritionAssessmentForm :enccode="patient?.enccode" :disabled="!patient?.height || !patient?.weight" @updated="update()" />
                </div>
                
                <Divider v-if="user.user_level === '59'" type="dashed" />

                <ScrollPanel class="h-[65vh]">
                    <div v-if="!Object.keys(history)?.length" class="container mx-auto flex flex-col justify-center items-center py-4 h-[50vh]">
                        <NoData class="text-primary" />
                        <p class="text-lg font-bold mt-4">No data found.</p>
                    </div>

                    <div v-else>
                        <div v-for="item in history" class="p-4">
                            <div class="flex flex-col gap-4 hover:cursor-pointer hover:text-primary-300" :class="{'text-primary': item === selected}" @click="selected = item">
                                <div class="w-full truncate">
                                    <span class="font-bold text-xl">{{ item.assessment }}</span>
                                </div>
                                
                                <div class="flex flex-col">
                                    <span class="italic font-light text-base">{{ item.firstname }} {{ item.middlename }} {{ item.lastname }}</span>
                                    <span class="italic font-light text-base">{{ formatDateTime(item.created_at) }}</span>
                                </div>
                            </div>
    
                            <Divider type="dashed" />
                        </div>
                    </div>
                </ScrollPanel>
            </div>

            <div class="col-span-6 md:col-span-4" :class="{'hidden md:block': !selected}">
                <div class="border border-dashed border-primary w-full h-[65vh] md:h-full rounded-sm p-4">
                    <div class="flex flex-wrap text-pretty break-words break-all">
                        <span>{{ selected?.assessment }}</span>
                    </div>
                </div>

                <div class="mt-2 text-right" :class="{'block md:hidden': selected, 'hidden': !selected}">
                    <Button text label="Close" class="w-max px-6 mb-1" @click="selected = null" />
                </div>
            </div>
        </div>
    </ViewTemplate>
</template>