export function usePrintable() {
    const show = (page, title) => {
        if(import.meta.client) {
            const print_window = window.open(page, '_blank', 'width=1071,height=621');

            if(!print_window) {
                alert('Popup blocked!');
                return;
            }

            print_window.document.title = `D I E T S - ${title}`;
        }
    }

    
    return { show };
}