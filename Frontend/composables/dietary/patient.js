export function usePatient() {
    const confirm = useConfirm();
    const user_store = useUserStore();
    const { user } = storeToRefs(user_store);
    const { show } = usePrintable();

    const enccode = ref();

    //  Confirm open patient details popup
    const openPatientDetailsConfirmation = (event) => {
        if(user.value?.user_level !== '59') return;

        enccode.value = event?.data?.enccode;
        
        confirm.require({
            target: event.originalEvent.currentTarget,
            group: 'headless',
        });
    }

    //  Show patient details dialog
    const openPatientDetails = (patient) => {
        if(user.value?.user_level !== '59') return;
        
        const enccode = encodeURIComponent(patient?.data?.enccode);
        
        show(`/doctors/patient/${enccode}`, 'Patient');
    }

    return { enccode, openPatientDetailsConfirmation, openPatientDetails };
  }