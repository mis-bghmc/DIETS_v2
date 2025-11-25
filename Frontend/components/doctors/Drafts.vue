<script setup>
import { DoctorsOrdersService } from '~/services/DoctorsOrdersService';

const toast = useToast();
const confirm = useConfirm();

const user_store = useUserStore();
const { user } = storeToRefs(user_store);

const { formatDateTime } = useDate();

const emit = defineEmits(['use-draft']);

const { data, error, status, refresh } = await useAsyncData(
    'doctors-draft', 
    () => DoctorsOrdersService.getDrafts(user.value.employeeid),
    {
        default: () => []
    }
);

const draft = ref();
const visible = ref(false);


//  Open draft
function openDraft() {
    if(draft.value) visible.value = true;
}

//  Use draft
function useDraft() {
    emit('use-draft', draft.value);
    visible.value = false;
}

//  Confirm delete draft
const confirmDeleteDraft = () => {
    confirm.require({
        group: 'delete-draft',
        accept: () => {
            deleteDraft();
        },
        reject: () => {
            
        }
    });
}

//  Delete draft
async function deleteDraft() {
    try{
        await DoctorsOrdersService.deleteDraft({
            body: {
                id: draft.value?.id,
            }
        });

        draft.value = null;
        visible.value = false;
        refresh();

        toast.add({ severity: 'success', summary: 'Success!', detail: 'Draft deleted successfully.', life: 5000 });

    }catch(error){
        toast.add({ severity: 'error', summary: 'Error!', detail: 'An error has occured. Please log it into the intranet or call extension 202. [Drafts -> delete]' });
    }
}

function resetDraft() {
    draft.value = null;
    visible.value = false;
}


//  Exposed functions
defineExpose({
    refresh
});
</script>

<template>
    <ViewTemplate :error="error" :status="status">

        <ConfirmDialog group="delete-draft" class="border border-primary rounded-md" pt:mask:class="backdrop-blur-sm">
            <template #container="{ message, acceptCallback, rejectCallback }">
                <div class="flex flex-col items-center p-8 bg-surface-0 dark:bg-surface-900 rounded">
                    <div class="rounded-full bg-red-500 text-primary-contrast inline-flex justify-center items-center h-24 w-24 -mt-20">
                        <i class="pi pi-exclamation-triangle !text-4xl"></i>
                    </div>
                    <span class="font-bold text-2xl block mb-2 mt-6"> Confirm Delete</span>
                    <p class="mb-0">Are you sure you want to delete this draft?</p>
                    <div class="flex items-center gap-2 mt-6">
                        <Button label="Cancel" severity="contrast" variant="outlined" @click="rejectCallback" class="w-32"></Button>
                        <Button label="Delete" severity="danger" @click="acceptCallback" class="w-32"></Button>
                    </div>
                </div>
            </template>
        </ConfirmDialog>

        <Listbox v-model="draft" :options="data"  option-label="title" class="listbox-border" filter filter-placeholder="Search Draft Title" @click="openDraft()" >
            <template #option="slotProps">
                <div class="flex justify-start items-center w-full gap-2" >
                    <Icon :name=" slotProps.option.category === 'Personal' ? 'mdi:person' : 'mdi:briefcase'" class="w-5 h-5 flex-shrink-0"/>
                    <p class="font-bold"> {{ slotProps.option.title }}</p>
                </div>
            </template>
        </Listbox>

        <Dialog 
            modal
            v-model:visible="visible" 
            :dismissableMask=true
            :draggable=false
            pt:root:class="!w-full md:!w-2/3 lg:!w-1/2 !h-max !border-primary !border-2 !rounded-md sm:!text-sm md:!text-base lg:!text-lg" 
            pt:header:class="!pb-2 !pt-3"
            pt:mask:class="!backdrop-blur-sm" 
            @after-hide="draft = null"
        >
            <template #header>
                <div class="flex gap-2 items-center">
                    <Icon name="material-symbols:draft" size="1.5rem" />
                    <span class="font-bold">Draft</span>
                </div>
                
            </template>

            <div class="flex flex-col gap-4 text-base">
                <div class="p-4">
                    <div class="flex gap-12">
                        <div class="col-span-1 flex flex-col gap-4 justify-start">
                            <span class="text-muted-color italic">Title</span>
                            <span class="text-muted-color italic">Category</span>
                            <span class="text-muted-color italic">Date created</span>
                            <span class="text-muted-color italic">Description</span>
                        </div>
    
                        <div class="col-span-2 flex flex-col gap-4 justify-start">
                            <span class="font-semibold">{{ draft?.title ?? '-' }}</span>
                            <span class="font-semibold">{{ draft?.category ?? '-' }}</span>
                            <span class="font-semibold">{{ formatDateTime(draft?.created_at) }}</span>
                            <span class="font-semibold">{{ draft?.description ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4 items-center justify-end border-t border-surface-200 pt-4">
                    <Button severity="danger" outlined @click="confirmDeleteDraft()">
                        <Icon name="fluent:delete-16-filled" size="1.5rem" />
                        <label class="font-bold hover:cursor-pointer">Delete Draft</label>
                    </Button>
                    <Button label="Use" @click="useDraft()">
                        <Icon name="fluent:drafts-16-filled" size="1.5rem" />
                        <label class="font-bold hover:cursor-pointer">Use Draft</label>
                    </Button>
                </div>
            </div>
        </Dialog>
    </ViewTemplate>
</template>


<style scoped>
.listbox-border {
    --p-listbox-border-color: var(--primary-contrast-color);
    --p-listbox-background: var(--primary-contrast-color);
    border-radius: 0%;
}

</style>