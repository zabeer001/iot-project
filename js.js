const mergeSortedArray = (arr1, arr2) => {
    let arr = [];
    let i = 0;
    let j = 0;

    while (i < arr1.length && j < arr2.length) {
        if (arr1[i] < arr2[j]) {
            arr.push(arr1[i]);
            i++;
        } else {
            arr.push(arr2[j]);
            j++;
        }
    }

    while (i < arr1.length) {   // ✅ fixed
        arr.push(arr1[i]);
        i++;
    }

    while (j < arr2.length) {   // ✅ fixed
        arr.push(arr2[j]);
        j++;
    }

    return arr;
};
var merge = function (nums1, m, nums2, n) {

    let arr1 = nums1.slice(0, m)
    let arr2 = nums2.slice(0, n)

    let arr = mergeSortedArray(arr1,arr2);

    return arr;



};

nums1 = [1,2,3,0,0,0], m = 3, nums2 = [2,5,6], n = 3

let result = merge(nums1, m, nums2, n);
console.log(result);
