function toolTip(force = true) {
    // Initialize variables
    var hideTimeout, tip = document.getElementById('tip0815') || createToolTip();

    // Hide the tooltip initially
    tip.style.display = 'none';
    // Get all elements with the 'title' attribute
    let objs = document.querySelectorAll('[title]');
    // Process each element with 'title' attribute
    objs.forEach((elem) => {
        // Store the original title in the 'data-tooltip' attribute
        if (elem.dataset.tooltip || force) {
            addTip(elem); // Attach event listeners
        }
    });
    // Function to attach event listeners to an element
    function addTip(elem) {
        if (elem.title) {
            elem.dataset.tooltip = elem.title;
            elem.title = ''; // Remove the original title
        }
        elem.removeEventListener('click', closeTip, false);
        elem.addEventListener('click', closeTip, false);
        elem.removeEventListener('mouseover', show, false);
        elem.addEventListener('mouseover', show, false);
        elem.onmouseout = () => hideTimeout = setTimeout(closeTip, 300);
    }

    // Function to show the tooltip
    function show(e) {
        tip.style.display = '';
        tip.innerHTML = this.dataset.tooltip;
        tip.style.position = 'absolute';
        tip.style.left = e.pageX + 'px';
        tip.style.top = 10 + e.pageY + 'px';
        hide();
    }

    // Function to handle hiding the tooltip
    function hide() {
        tip.onmouseout = () => hideTimeout = setTimeout(closeTip, 300);
        tip.onmouseover = () => clearTimeout(hideTimeout);
    }
    // Function to close the tooltip
    function closeTip() {
        tip.style.display = 'none';
    }
    function createToolTip() {
        var newTip = document.createElement('DIV');
        newTip.id = 'tip0815';
        newTip.style.width = 'fit-content';
        newTip.style.backgroundColor = 'aliceblue';
        newTip.style.borderRadius = '6px';
        newTip.style.borderColor = 'black';
        newTip.style.borderStyle = 'solid';
        newTip.style.borderWidth = '1px';
        newTip.style.padding = ' 4px';
        newTip.style.zIndex = heighestZIndex() ;
        document.body.appendChild(newTip);
        return newTip;
    }
    function heighestZIndex() {// return highest Z-Index along the parent path
        var list, z = 51, zz = 0;
        list = document.querySelectorAll("[style*='z-index:']");
        list.forEach((elem) => {
            zz = parseInt(elem.style.zindex, 10);
            if (zz > z) {
                z = zz;
            }
        });
        return z;
    }
    return{
        addTip: addTip // for elements added after calling toolTip
    };
}