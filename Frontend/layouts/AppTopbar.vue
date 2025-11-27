<script setup>
import AppConfigurator from './AppConfigurator.vue';
import { AuthService } from "~/services/AuthService";

const { toggleMenu, toggleDarkMode, isDarkTheme } = useLayout();

const echo = useEcho();
const toast = useToast();
const route = useRoute();

const user_store = useUserStore();
const { user } = storeToRefs(user_store);

const is_dietitian = ref(user.value.user_level === '59');
const is_server = ref(user.value.user_level === '60');
const is_validUser = computed(() => is_dietitian.value || is_server.value);
const is_loggedOut = ref(false);
const is_connected = ref(false);
const is_loadingLogout = ref(false);
const unread = ref(0);
const notification = ref();
const prompt = ref(false);
const showInternetDialog = ref(false);
const refreshing = ref(false);
const config = useRuntimeConfig();

const { isOnline } = useOnline()

// ANCHOR Logout
async function onClick_Logout() {
    if(is_loggedOut.value) return;
    
    is_loggedOut.value = true;
    is_loadingLogout.value = true;
    try {
        await AuthService.logout();

        user_store.removeUserDetails();

        navigateTo('/auth/login');

    } catch(error) {
        is_loggedOut.value = false;

        toast.add({ severity: 'error', summary: 'Error!', detail: 'An error has occured. Please log it into the intranet or call extension 202. [Logout]' });
    } finally {
        is_loadingLogout.value = false;
    }
}

//  Check for unread notifications
function checkForUnread(_count) {
    unread.value = _count;
}

//  Refresh page
function refreshPage() {
    refreshing.value = true;
    window.location.reload();
}

//  Feedback
function onClickFeedback() {
    window.open( config.public.FEEDBACK_URL, '_blank', 'width=1071,height=621');
}

//  User manual
function openUserManual() {
    window.open('/documents/user-manual.pdf', '_blank', 'width=1071,height=621');
}

//  Online
function notifyUser() {
    if(!isOnline.value) {
        showInternetDialog.value = true;
        return;
    } 

    toast.add({ severity: 'success', summary: 'Connected!', detail: 'You are now connected to the network!', life: 3000 });
}

//  Watcher for when network is 
watch(
  isOnline,
  (newVal, oldVal) => {
    if (newVal !== oldVal) {
      notifyUser()
    }
  }
)



//  On mounted
onMounted(()=> {
    //  Listener for dietitians and food servers only
    if(is_validUser.value) {
        const connection = echo.connector.pusher.connection;
    
        is_connected.value = connection.state === 'connected';

        connection.bind('state_change', ({ current }) => {
            is_connected.value = current === 'connected';
        });
        
        echo.channel('notification-channel')    
            .listen('.new-doctors-order', (e) => {
                
                if(!['Priority', 'Late'].includes(e.message)) return;
                
                nextTick(async () => {
                    notification.value?.refreshNotifications();
                    notification.value?.playNotificationSfx();
                })

            })
            .listen('.scheduled-update', () => {
                prompt.value = true;
            });
    }
});
</script>

<template>
    <div class="container mx-auto">
        <Dialog 
            v-model:visible="prompt" 
            modal 
            :closeOnEscape="false"
            pt:root:class="!px-8 !pt-6 !pb-4" 
            pt:mask:class="!backdrop-blur-sm" 
            :class="{'!bg-transparent !border-0 !shadow-none': refreshing, '!bg-inherit !border-2 !border-primary': !refreshing}"
        >
            <template #container>
                <div v-if="refreshing">
                    <LoaderEgg />
                </div>
                <div v-else>
                    <div class="flex items-center text-white mb-2">
                        <i class="pi pi-exclamation-circle !text-3xl mr-2 text-primary"></i>
                        <span>Diet list have been updated.</span>
                    </div>
                    <div class="w-full flex justify-end">
                        <Button label="Refresh" class="w-20 bg-inherit border-primary" pt:label:class="text-white" @click="refreshPage()" />
                    </div>
                </div>
            </template>
        </Dialog>
    </div>

    <div class="container mx-auto">
        <Dialog 
            v-model:visible="showInternetDialog" 
            modal 
            :closeOnEscape="false"
            pt:root:class="!px-8 !pt-6 !pb-4" 
            pt:mask:class="!backdrop-blur-sm" 
            :class="{'!bg-transparent !border-0 !shadow-none': refreshing, '!bg-inherit !border-2 !border-red-500': !refreshing}"
        >
            <template #container>
                <div v-if="refreshing">
                    <LoaderEgg />
                </div>
                <div v-else>
                    <div class="flex items-center text-white mb-2">
                        <i class="pi pi-exclamation-circle !text-3xl mr-2 text-red-500"></i>
                        <span>You are currently offline!</span>
                    </div>
                    <Divider />
                    <div class="text-sm text-white">
                        <p> • Do not refresh the page until you are back online.</p>
                        <p> • To report this issue, you may call us at extension 202 or report it through Intranet.</p>
                    </div>
                    <div class="w-full mt-10 flex justify-center">
                        <Button label="I Understand" class="bg-inherit border-primary" pt:label:class="text-white" @click="showInternetDialog = false" />
                    </div>
                </div>
            </template>
        </Dialog>
    </div>

    <div class="layout-topbar">
        <div class="layout-topbar-logo-container">
            <button v-if="is_dietitian && !route.path.startsWith('/doctors')" class="layout-menu-button layout-topbar-action" @click="toggleMenu">
                <i class="pi pi-bars"></i>
            </button>
            <div class="layout-topbar-logo">
                <NuxtLink to="/">
                    <span>D I E T S</span>
                </NuxtLink>
            </div>
        </div>

        <div class="layout-topbar-actions">
            <div class="layout-config-menu">
                
                <div v-if="!isOnline" class="relative">
                    <i class="pi pi-wifi !text-red-500 !text-4xl !animate-pulse" v-tooltip.bottom="'No Network Connection!'"/>
                </div>
                
                 
                <div v-if="is_dietitian && !route.path.startsWith('/doctors')" class="relative">
                    <button 
                        v-styleclass="{ selector: '@next', enterFromClass: 'hidden', enterActiveClass: 'animate-scalein', leaveToClass: 'hidden', leaveActiveClass: 'animate-fadeout', hideOnOutsideClick: true }"
                        type="button" 
                        class="text-primary border-0 border-primary rounded-full w-10 h-10 pt-1"
                    >
                        
                        <OverlayBadge v-if="unread" severity="danger">
                            <i :class="['!text-4xl !text-primary layout-topbar-logo', {'pi pi-bell': is_connected, 'pi pi-bell-slash': !is_connected}]"></i>
                        </OverlayBadge>
                        
                        <i v-else :class="['!text-4xl !text-primary layout-topbar-logo', {'pi pi-bell': is_connected, 'pi pi-bell-slash': !is_connected}]"></i>
                    </button>

                    <div class="absolute hidden right-0">
                        <div class="fixed lg:relative bg-[--surface-overlay] border-2 border-primary rounded mt-1 p-4 z-50 left-1/2 -translate-x-1/2 h-auto w-[calc(100vw-2rem)] lg:w-[50vw]">
                            <Notification ref="notification" @unread="checkForUnread"/>
                        </div>
                    </div>
                </div>

               
               
                <div v-if="route.path.startsWith('/doctors')">
                    <SearchPatients />
                </div>

                <Divider v-if="is_dietitian || route.path.startsWith('/doctors')" layout="vertical" />

                <div class="relative">
                    <button
                        v-styleclass="{ selector: '@next', enterFromClass: 'hidden', enterActiveClass: 'animate-scalein', leaveToClass: 'hidden', leaveActiveClass: 'animate-fadeout', hideOnOutsideClick: true }"
                        type="button"
                        class="text-primary border-2 border-primary rounded-full w-10 h-10 mt-[2.5px]"
                    >
                        <i class="pi pi-user !text-xl !text-primary layout-topbar-logo"></i>
                    </button>
                    
                    <div class="absolute hidden right-0 mt-1 w-64 border-2 border-primary p-4 rounded h-auto z-50 sm:text-sm md:text-base lg:text-lg bg-[--surface-overlay]">
                        <div>
                            <div class="font-bold">{{ user?.firstname }} {{  user?.middlename }} {{ user?.lastname}}</div>
                            <div class="italic text-xs">{{ user?.postitle }}</div>
                        </div>

                        <Divider />

                        <div class="flex items-center w-full">
                            <span class="text-sm text-muted-color font-semibold flex-1">Dark mode</span>
                            <ToggleSwitch :model-value="isDarkTheme" @change="toggleDarkMode" />
                        </div>
                        
                        <div>
                            <AppConfigurator />
                        </div>

                        <div class="flex flex-col mt-6">
                            <Button variant="text" severity="secondary" class="!text-sm" @click="onClickFeedback">
                                <Icon name="fluent:person-feedback-48-filled" size="1.5em" />
                                <label class="hover:cursor-pointer">Feedback & Suggestions</label>
                            </Button>
    
                            <Button v-if="is_dietitian" variant="text" severity="secondary" class="!text-sm !flex !justify-start" @click="openUserManual()">
                                <Icon name="game-icons:black-book" size="1.5em" />
                                <label class="hover:cursor-pointer">User Manual</label>
                            </Button>
                        </div>

                        <Divider />

                        <div class="flex justify-end items-center gap-4 p-0">
                            <Button 
                                text 
                                :disabled="is_loggedOut" 
                                class="p-0 hover:!bg-transparent"
                                @click="onClick_Logout"
                                :loading="is_loggedOut"
                            >
                                <Icon name="fluent:sign-out-24-regular" size="1.8em" class="text-primary hover:cursor-pointer" />
                                <label class="font-bold text-base hover:cursor-pointer">{{ is_loggedOut ? 'Logging out...' : 'Logout' }} </label>
                            </Button>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
