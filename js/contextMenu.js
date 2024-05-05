function contextMenuF(id, owner) {
    'use strict';

    // Variables
    let contextMenu, context_timeout;
    let innerMenu = '';
    let styleElem;

    // Handle id parameter
    if (typeof id === 'string' && id !== '') {
        innerMenu = document.getElementById(id);
    } else if (typeof id === 'object') {
        innerMenu = id;
    }

    // Create context menu styling
    if (!document.querySelector('.liHover')) {
        styleElem = document.createElement('STYLE');
        styleElem.innerHTML = ".liHover:hover{cursor:pointer}";
        document.head.appendChild(styleElem);
    }

    // Create context menu element
    contextMenu = document.createElement('DIV');
    contextMenu.dataset.owner = owner;
    contextMenu.style.display = 'none';
    contextMenu.style.padding = '10px';
    contextMenu.style.border = '1px solid #efefef';
    contextMenu.style.borderRight = '2px solid #efefef';
    contextMenu.style.borderBottom = '2px solid #efefef';
    contextMenu.style.backgroundColor = '#ececec';
    
    // Set up inner menu
    if (innerMenu) {
        setUpInnerMenu();
    } else {
        createEmptyMenu();
    }

    // Event handling
    contextMenu.classList.add('contextParent', 'box');
    contextMenu.addEventListener('click', close);
    document.body.appendChild(contextMenu);
    contextMenu.querySelectorAll('li').forEach(elem => elem.classList.add('liHover'));

    // Functions
    function setUpInnerMenu() {
        checkType(innerMenu);
        contextMenu.id = innerMenu.id;
        innerMenu.id = '';
        contextMenu.appendChild(innerMenu);
    }

    function createEmptyMenu() {
        contextMenu.id = new Date().getTime();
        contextMenu.innerHTML = "<ul></ul>";
        innerMenu = contextMenu.firstChild;
    }

    function addMenue(men) {
        men.forEach(elem => add(elem.id, elem.icon, elem.label, elem.handler));
    }

    function add(id, icon, text, handler) {
        if (id === '__sep__') {
            sep();
            return;
        }
        const ul = contextMenu.firstChild;
        ul.insertAdjacentHTML('beforeend', `<li class='liHover' data-id=${id}>${icon}${text}`);
        if (typeof handler === 'function') {
            addHandler(id, handler);
        }
    }

    function addHandler(id, handler) {
        const elem = getEntry(id);
        elem.onclick = '';
        elem.removeEventListener('click', handler, false);
        elem.addEventListener('click', handler, false);
    }

    function sep() {
        const ul = contextMenu.firstChild;
        ul.insertAdjacentHTML('beforeend', `<hr>`);
    }

    function remove(id) {
        const elem = getEntry(id);
        if (elem) {
            elem.parentNode.removeChild(elem);
        }
    }

    function hide(id) {
        const elem = getEntry(id);
        if (elem) {
            elem.style.display = 'none';
        }
    }

    function show(id) {
        const elem = getEntry(id);
        if (elem) {
            elem.style.display = '';
        }
    }

    function getEntry(id) {
        const ul = contextMenu.firstChild;
        return ul.querySelector(`[data-id='${id}']`);
    }

    function open(e) {
        e.stopPropagation();
        e.preventDefault();
        let left = e.pageX;
        let top = e.pageY;

        // *****************************************
        // render and freeze size
        // ******************************************

        contextMenu.style.display = 'inline-block';
        contextMenu.style.position = 'absolute';
        contextMenu.style.padding = '10px';
        contextMenu.style.width = 10 + contextMenu.clientWidth + 'px';

        // Handle edge cases
        const bedge = window.pageYOffset + window.innerHeight;
        const redge = window.pageXOffset + window.innerWidth;
        if (left + contextMenu.clientWidth >= redge) {
            left -= left + contextMenu.clientWidth - redge + 20;
            contextMenu.style.left = `${left}px`;
        }
        if (top + contextMenu.clientHeight >= bedge) {
            top -= top + contextMenu.clientHeight - bedge + 20;
            contextMenu.style.top = `${top}px`;
        }
        // *****************************************
        // now move in place
        // ******************************************
        contextMenu.style.left = `${left - 5}px`;
        contextMenu.style.top = `${top - 5}px`;


        // Handle z-index and timeouts
        contextMenu.style.zIndex = heighestZIndex(e.target);
        contextMenu.target = e.target;
        contextMenu.focus();
        contextMenu.onmouseout = () => context_timeout = setTimeout(close, 300);
        contextMenu.onmouseover = () => clearTimeout(context_timeout);
    }

    function close() {
        contextMenu.style.display = 'none';
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
    function checkType(cm) {
        if (cm.dataset.type === 'json') {
            try {
                const jstext = JSON.parse(cm.childNodes[1].substringData(4, -1));
                cm.innerHTML = '';
                jstext.forEach(elem => {
                    cm.insertAdjacentHTML('beforeend', `<li class='liHover'  data-id=${elem.id}>${elem.icon}${elem.label}`);
                    if (typeof elem.handler === 'function') {
                        addHandler(elem.id, elem.handler);
                    }
                });
            } catch (ee) {
            }
        }
    }

    return {
        id: () => contextMenu.id,
        add,
        addMenue,
        addHandler,
        remove,
        open,
        close,
        hide,
        show,
        getEntry
    };
}
