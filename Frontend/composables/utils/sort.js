export const useSort = () => {
    function sortArray(array, sort_by){
        return array.sort((a, b) => 
            a[sort_by].localeCompare(b[sort_by], undefined, { sensitivity: 'base' })
        );
    }

    return {sortArray};
}