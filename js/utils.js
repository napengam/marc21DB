function utils() {

    function absPos(objs, parent) {// return absolute x,y position of obj
        var ob, x, y, obj;
        obj = gob(objs);
        if (obj === null) {
            return {'x': 0, 'y': 0, 'w': 0, 'h': 0, 'o': null};
        }
        if (typeof parent === 'undefined') {
            ob = obj.getBoundingClientRect();
            return {'x': ob.left + window.scrollX, 'y': ob.top + window.scrollY, 'w': ob.width, 'h': ob.height, 'o': obj};
        }
        x = obj.offsetLeft, y = obj.offsetTop;
        ob = obj.offsetParent;
        while (ob !== null && ob !== parent) {
            x += ob.offsetLeft;
            y += ob.offsetTop;
            ob = ob.offsetParent;
        }
        return {'x': x, 'y': y, 'w': obj.clientWidth, 'h': obj.clientHeight, 'o': obj};
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

    function mapFunctions(id, selectorClass, objectOfFunctions) {
        'use strict';
        var elements, obj;

        obj = gob(id);
        if (obj === null) {
            return;
        }

        if (obj.classList.contains(selectorClass)) {
            mapFunc(obj);
        }
        elements = obj.querySelectorAll(selectorClass);
        elements.forEach(mapFunc);// iterate over list of DOM elements

        function mapFunc(element) { // handle one element
            var funameList, // array of function names
                    eventList, // array of event names
                    funame; // a function name
            eventList = element.dataset.event ? element.dataset.event.split(',') : [];
            funameList = element.dataset.funame ? element.dataset.funame.split(',') : [];
            while (eventList.length < funameList.length) {
                eventList.push('click'); // default
            }
            eventList.forEach(mapEvent);
            function mapEvent(event) {
                funame = funameList[0].split('.').pop(); // get last part of name 
                if (typeof objectOfFunctions[funame] !== 'undefined' && typeof objectOfFunctions[funame] === 'function') {
                    element.removeEventListener(event, objectOfFunctions[funame], false);
                    element.addEventListener(event, objectOfFunctions[funame], false); // attache function for this event
                }
                funameList.shift();
            }
        }
    }
    function gob(id) { // return object
        let o;
        if (typeof id === 'string') {
            o = document.getElementById(id);
        } else if (typeof id === 'object') {
            o = id;
        } else {
            return null;
        }
        return o;
    }

    let funcs = {
        absPos: absPos,
        heighestZIndex: heighestZIndex,
        mapFunctions: mapFunctions,
        gob: gob
    };
    return funcs;

}
