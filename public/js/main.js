var profileCard = document.getElementById("profileCard");
// const profileDialog = document.getElementById("profileDialog");
// const editProfileButton = document.getElementById("editProfileButton");
var logoutButton = document.getElementById("logoutButton");
var changePasswordButton = document.getElementById("changePasswordButton");
var webUrl = "https://mstarlabsoftwear.com/";

var font1 = "'Poppins', sans-serif";
var backgroundColor = '#f1f7ff';
var lightgray = '#EEEEEE';
var buttoncolor = '#5e9ff3';
var lightdarkgray = '#cccccc';

const commonSummernoteOptions = {
    toolbar: [
        ['history', ['undo', 'redo']],
        ['style', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
        ['font', ['fontname', 'fontsize', 'color', 'superscript', 'subscript']],
        ['para', ['ul', 'ol', 'paragraph', 'height', 'lineheight']],
        ['insert', ['link', 'picture', 'video', 'table', 'hr']],
        ['misc', ['codeview', 'fullscreen', 'help']],
        ['style', ['style']]
    ],
}

if (profileCard != null) {
    profileCard.addEventListener("click", () => {
        $("#profileDialog").appendTo("body").modal('show');
    });
}

if (changePasswordButton != null) {
    changePasswordButton.addEventListener("click", () => {
        closeDialog();
        $("#changePasswordDialog").appendTo("body").modal('show');
    });
}

function closeDialog() {
    $("#profileDialog").appendTo("body").modal('hide');
}

if (logoutButton != null) {
    logoutButton.addEventListener("click", () => {
        closeDialog();
        goToRoute('logout');
    });
}

function goToRoute(routeName) {
    var routeUrl = webUrl + routeName;
    window.location.href = routeUrl;
}

function openNewTab(routeName) {
    var routeUrl = webUrl + routeName;
    window.open(routeUrl, '_blank');
}

function searchResultArrowSelect(searchInputId, searchListId) {
    const searchInput = document.getElementById(searchInputId);
    const searchList = document.getElementById(searchListId);
    const inputSpansList = searchList.getElementsByTagName('div');
    let selectedIndex = -1;

    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowUp') {
            event.preventDefault();
            if (selectedIndex > 0) {
                selectedIndex--;
                updateSelectedSpan(inputSpansList, selectedIndex);
            }
        } else if (event.key === 'ArrowDown') {
            event.preventDefault();
            if (selectedIndex < inputSpansList.length - 1) {
                selectedIndex++;
                updateSelectedSpan(inputSpansList, selectedIndex);
            }
        } else if (event.key === 'Enter') {
            if (selectedIndex >= 0 && selectedIndex < inputSpansList.length) {
                inputSpansList[selectedIndex].click();
            }
        }
    });
}

function updateSelectedSpan(inputSpansList, selectedIndex) {
    for (let i = 0; i < inputSpansList.length; i++) {
        if (i === selectedIndex) {
            inputSpansList[i].classList.add('search_selected');
            ensureVisible(inputSpansList[i]);
        } else {
            inputSpansList[i].classList.remove('search_selected');
        }
    }
}

function ensureVisible(element) {
    const scrollParent = getScrollParent(element);
    if (!scrollParent) {
        return;
    }

    const elementRect = element.getBoundingClientRect();
    const parentRect = scrollParent.getBoundingClientRect();

    if (elementRect.bottom > parentRect.bottom) {
        scrollParent.scrollBy({ top: elementRect.bottom - parentRect.bottom, behavior: 'smooth' });
    } else if (elementRect.top < parentRect.top) {
        scrollParent.scrollBy({ top: elementRect.top - parentRect.top, behavior: 'smooth' });
    }
}

function getScrollParent(node) {
    if (node == null) {
        return null;
    }

    if (node.scrollHeight > node.clientHeight) {
        return node;
    } else {
        return getScrollParent(node.parentNode);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('header').style.height = (parseInt(getComputedStyle(document.getElementsByClassName('actions')[0]).height) + 35) + 'px';
});

function isDivEmpty(divId) {
    var div = document.getElementById(divId);
    var content = div.innerHTML.trim();

    var tempDiv = document.createElement('div');
    tempDiv.innerHTML = content;

    Array.from(tempDiv.childNodes).forEach(function (node) {
        if (node.nodeType === Node.ELEMENT_NODE) {
            if (node.textContent.trim() === '' && !['BR', 'IMG'].includes(node.tagName)) {
                node.remove();
            }
        }
    });

    return tempDiv.textContent.trim() === '';
}

function toggleDivVisibility(divId, targetId) {
    $('#' + targetId).css('display', isDivEmpty(divId) ? 'none' : 'revert');
}
