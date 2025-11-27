<script setup>
import { NotificationsService } from "~/services/NotificationsService";
import { useSound } from '@vueuse/sound';
import notificationSfx from '../assets/audio/alert-notification.mp3';
import NoData from '~/public/images/no-data.svg';

const emit = defineEmits(['unread']);

const echo = useEcho();
const toast = useToast();

const { play } = useSound(notificationSfx);
const { isWithinGracePeriod } = useDietValidation(); 
const { formatMonthShort, formatTime } = useDate();

const user_store = useUserStore();
const { user } = storeToRefs(user_store);

const accepted = ref();
const acknowledged = ref();

const date_range = ref(0);

const { data: notifications, error, status, refresh } = await useAsyncData(
    'notifications', 
    () => NotificationsService.getNotifications(date_range.value),
    {
        default: () => [],
    }
);

const filtered_notifications = ref(notifications.value);

const options_status = [
    { label: 'All', value: 'A' },
    { label: 'Pending', value: 'P' },
    { label: 'Acknowledged / Accepted', value: 'AA' },
];

const options_priority = [
    { label: 'All', value: 'A' },
    { label: 'Priority', value: 'Y' },
    { label: 'Normal', value: 'N' },
];

const options_ward = computed(() => {
    const result = Object.groupBy(filtered_notifications.value ?? [], ({wardname}) => wardname);
    const options = Object.keys(result).map((key) => {
        return { label: key, value: key }
    });
    options.unshift({ label: 'All', value: 'A' });
    return options;
});

const options_date = computed(() => {
    const result = Object.groupBy(filtered_notifications.value ?? [], ({dodate}) => formatMonthShort(dodate));
    const options = Object.keys(result).map((key) => {
        return { label: key, value: key }
    });
    options.unshift({ label: 'All', value: 'A' });
    return options;
});

const filters = ref({
    status: options_status, 
    priority: options_priority, 
    wards: options_ward, 
    date: options_date
});

const filter_object = ref({
    status: 'A',
    priority: 'A',
    wards: 'A',
    date: 'A'
});


//  Filter notifications
function filterNotifications() {
    const filtered = notifications.value.filter(filterValidation);
    filtered_notifications.value = filtered;
}

//  Callback function for filtering notifications
function filterValidation(notification) {
    const { status, priority, wards, date } = filter_object.value;
    const statusMatch =
        status === 'A' ||
        (status === 'P' && !notification.seen_date) ||
        (status === 'AA' && notification.seen_date);

    const priorityMatch = priority === 'A' || priority === notification.priority;
    const wardMatch = wards === 'A' || wards === notification.wardname;
    const dateMatch = date === 'A' ? true : date === formatMonthShort(notification.dodate);
    
    return statusMatch && priorityMatch && wardMatch && dateMatch;
};

//  Clear notification filter
function clearFilter() {
    filter_object.value = {
        status: 'A',
        priority: 'A',
        wards: 'A',
        date: 'A'
    };
}

//  Show only todays notifications
async function seeTodaysNotifications() {
    date_range.value = 0;
    refreshNotifications();
}

//  Show all notifications
async function seeAllNotifications() {
    date_range.value = 30;
    refreshNotifications();
}

//  Format Select option labels
function formatFilterLabel(_label){
    return _label.charAt(0).toUpperCase() + _label.slice(1);
}

//  Parse message
function parseMessage(_message, _type) {
    if(!_message) return;

    try{
        const message = JSON.parse(_message);
    
        return [
            message[`${_type}_diet`],
            message[`${_type}_ons`],
            message[`${_type}_ons2`]
        ]
        .filter(Boolean)
        .join(' + ');

    }catch {
        const change = _message?.split('from')?.[1] || [];
        const diets = change?.split('to') || [];
        
        return _type === 'from' ? diets?.[0] : diets?.[1];
    }
}

//  Get button label based on notification details
function getLabel(_notifs){
    if(accepted.value === _notifs.docointkey || acknowledged.value === _notifs.docointkey) return;

    if(_notifs.priority === 'Y'){
        return _notifs.seen_date ? 'Acknowledged' : 'Acknowledge';
    }

    return _notifs.pd_docointkey && _notifs.locked === 'Y' ? 'Accepted' : 'Accept';
}

//  Check button disabled status
function isDisabled(_notifs){
    return _notifs.priority === 'Y' ? _notifs.seen_date : _notifs.locked === 'Y';
}

//  Refetch notifications and check for unread
async function refreshNotifications() {
    await refresh();
    checkForUnread();
    clearFilter();

    filtered_notifications.value = notifications.value;
}

//  Check for unread notifications
function checkForUnread() {
    if(date_range.value) return;
    
    const unread =  notifications.value.filter(n => (n.seen_date === null && n.priority === 'Y') || (n.seen_date === null && n.priority === 'N' && n.locked !== 'Y'));
    
    emit('unread', unread.length);
}

//  Play notificaion sound effect
function playNotificationSfx() {
    play();
}

//  Accept late doctors order
async function acceptLateUpdate(_docord){
    if(accepted.value) return;
    
    accepted.value = _docord.docointkey;
    
    try{
        if(!isWithinGracePeriod()){
            toast.add({ severity: 'error', summary: 'Update Failed!', detail: 'Out of acceptance time range.', life: 5000 });
            return;
        }

        const data = {
            headers: {
                'X-Socket-Id' : echo.socketId()
            },
            body: {
                docointkey: _docord.docointkey,
                enccode: _docord.enccode,
                hpercode: _docord.hpercode,
                ons_frequency: _docord.onsFrequency,
                updated_by: user.value.employeeid
            }
        };

        await NotificationsService.acceptLateUpdate(data);

        _docord.pd_docointkey = _docord.docointkey;
        _docord.seen_date = new Date();
        _docord.locked = 'Y';

        checkForUnread();

    }catch(error){
        toast.add({ severity: 'error', summary: 'Error!', detail: 'An error has occured. Please log it into the intranet or call extension 202. [Notifications -> accept]' });
        
    }finally{
        accepted.value = null;
    }
}

//  Acknowledge doctors order
async function acknowledge(_docord){
    if(acknowledged.value) return;

    acknowledged.value = _docord.docointkey;

    try{
        const data = {
            headers: {
                'X-Socket-Id' : echo.socketId()
            },
            body: {
                docointkey: _docord.docointkey,
                updated_by: user.value.employeeid
            }
        };

        await NotificationsService.acknowledge(data);

        _docord.seen_date = new Date();

        checkForUnread();

    }catch(error){
        toast.add({ severity: 'error', summary: 'Error!', detail: 'An error has occured. Please log it into the intranet or call extension 202. [Notifications -> acknowledge]' });

    }finally{
        acknowledged.value = null;
    }
}


//  Expose functions to parent
defineExpose({
    checkForUnread,
    refreshNotifications,
    playNotificationSfx
});

// On Mounted
onMounted(() => {
    checkForUnread();
});

</script>

<template>
    <div class="container mx-auto h-[85vh] sm:text-sm md:text-base lg:text-lg relative">
        <div class="w-full h-16 border-b-2 border-primary flex items-center px-2 pb-3">
            <div class="font-bold text-xl flex-1">
                <span class="hover:cursor-pointer" @click="seeTodaysNotifications()">Notifications</span>
            </div>
            <div class="underline">
                <span class="hover:cursor-pointer" @click="seeAllNotifications()">See all</span>
            </div>
        </div>
        
        <div class="h-[90%] overflow-y-auto">
            <div class="flex flex-col gap-4 p-2">
                <div>
                    <p>
                        <span class="font-semibold">Total: </span>
                        <span>{{ filtered_notifications?.length || 0 }}</span>
                    </p>
                </div>
        
                <div class="flex flex-wrap gap-2">
                    <template v-for="(value, key) in filters">
                        <FloatLabel v-if="(key !== 'date' && !date_range) || date_range" class="w-auto" variant="on">
                            <Select class="w-full" :id="key" :options="value" optionLabel="label" optionValue="value" v-model="filter_object[key]" @change="filterNotifications()">
                                <template #option="{ option }">
                                    <div class="py-2">
                                        <span>{{ option.label }}</span>
                                    </div>
                                </template>
                            </Select>
                            <label :for="key">{{ formatFilterLabel(key) }}</label>
                        </FloatLabel>
                    </template>
                </div>
            </div>
        
            <Divider />

            <ViewTemplate :error="error" :status="status">
                <div v-if="!filtered_notifications?.length" class="container mx-auto flex flex-col justify-center items-center py-4 h-[50vh]">
                    <NoData class="text-primary" />
                    <p class="text-lg font-bold mt-4">No data found.</p>
                </div>
    
                <div v-else v-for="notifs in filtered_notifications" :key="notifs.docointkey">
                    <div class="pr-2">
                        <div class="grid grid-cols-12">
                            <div class="col-span-1 flex items-center justify-center">
                                <Icon name="material-symbols:notification-important" class="text-4xl" :class="isDisabled(notifs) ? 'text-gray-200' : 'text-orange-600'" />
                            </div>
        
                            <div class="col-span-11 flex flex-wrap"> 
                                <div class="flex flex-col w-full">
                                    <div class="font-bold">{{ notifs.patname }}</div>
                                    <div>{{ notifs.wardname }}</div>

                                    <div class="italic my-1">
                                        <span>Diet {{ notifs.priority === 'Y' ? 'changed' : 'requested' }} from </span>
                                        <span class="text-red-400 font-semibold">{{ parseMessage(notifs.message, 'from') }}</span>
                                        <span> to </span>
                                        <span class="text-emerald-400 font-semibold">{{ parseMessage(notifs.message, 'to') }}</span>
                                    </div>

                                    <div>
                                        <div v-if="!date_range" class="flex items-end">
                                            <span class="text-sm italic flex-1">{{ formatTime(notifs.dodate) }}</span>
                                            <Button 
                                                pt:root:class="!min-w-32"
                                                :label="getLabel(notifs)" 
                                                :disabled="isDisabled(notifs)" 
                                                :loading="accepted === notifs.docointkey || acknowledged === notifs.docointkey"
                                                @click="notifs.priority === 'Y' ? acknowledge(notifs) : acceptLateUpdate(notifs)" 
                                            />
                                        </div>

                                        <div v-else class="flex flex-col text-sm">
                                            <div>
                                                <span>Requested: {{ formatMonthShort(notifs.dodate) }} {{ formatTime(notifs.dodate) }}</span>
                                            </div>
                                            <div v-if="notifs.seen_date">
                                                <span>{{ getLabel(notifs) }}: {{ formatMonthShort(notifs.seen_date) }} {{ formatTime(notifs.seen_date) }} by {{ notifs.firstname }} {{ notifs.lastname }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <Divider type="dashed" />
                </div>
            </ViewTemplate>
        </div>
    </div>
</template>