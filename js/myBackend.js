/* global eval */
GEVAL = {};
function myBackend(v)
{
    'use strict';
    var
            request, sendPkg, backEnd, respondAction,
            queue = [], timeOut = 0,
            setVeil = true, noQueue = false,
            veil, reveal = {};
    //
    // these functions will be returned to the caller of this module
    //
    reveal = {
        useVeil: useVeil, // (true||false);
        callDirect: callDirect, //(backEndScript, sendPkg, respondAction)    
        setTimeout: setTimeout, //(milliSeconds) default=1000
        setNoQueue: setNoQueue, //(true||false)
        fetchHTML: fetchHTML // (pfid, backend_script, payload, onDoneFunc)
    };
    if (typeof v !== 'undefined') {
        setVeil = false;
    }
    function dummy() {
        return true;
    }
    request = new XMLHttpRequest();
    if (!request || (request.readyState !== 4 && request.readyState !== 0)) {
        queue.length = 0;
        return;
    }
    //********************************************
    //  modal dialog while working
    //********************************************

    veil = document.createElement('DIALOG');
    veil.id = 'fetchBackend';
    veil.style.visibility = 'hidden';
    document.body.appendChild(veil);
    let styleElem = document.createElement('STYLE');
    styleElem.innerHTML = [
        "#fetchBackend::backdrop{opacity:0;background:red;position:fixed;top:0px;right:0px;bottom:0px;left:0px;}"
    ].join('');
    document.getElementsByTagName('head')[0].appendChild(styleElem);
    //
    // send very first request imediatly
    // then queue requests
    //
    function callDirect(backEnd, sendPkg, respondAction) {
        if (respondAction === '') {
            respondAction = dummy;
        }
        queue.push({
            'backEnd': backEnd,
            'sendPkg': JSON.stringify(sendPkg),
            'respondAction': respondAction
        });
        if (queue.length === 1 || noQueue) {
            callCore(); // very first request or no queueing
        }
    }
    //
    // send request imediatly
    //
    function callCore() {

        if (queue.length === 0) {
            return;
        }
        //************************************************
        // process first request in queue
        //************************************************
        sendPkg = queue[0].sendPkg;
        respondAction = queue[0].respondAction;
        backEnd = queue[0].backEnd;

        request.open("POST", backEnd, true);
        request.setRequestHeader("Content-Type", "application/json");
        request.onreadystatechange = onChange;
        request.timeout = timeOut;
        request.ontimeout = timedOut;
        //
        // activate veil. to avoid any user interaction until request 
        // is finished or timed out;
        //   
        if (setVeil) {
            veil.showModal();
        }
        request.send(sendPkg);
    }
    function onChange() {
        var js;
        if (this.readyState !== 4 || this.status !== 200) {
            if (this.readyState === 4) {
                queue.shift();
                veil.close();
                respondAction({'error': this.responseText});
                callCore();// process any remaining requests in queue
            }
            return;
        }
        // request comes back, take away veil. to allow user action
        this.onreadystatechange = '';
        veil.close();
        queue.shift();
        try {
            js = JSON.parse(this.responseText);
        } catch (e) {
            respondAction({'error': '<div style="width:60%;word-wrap: break-word;">' + this.responseText + e.message + '</div>'});
            callCore();// process any remaining requests in queue
            return;
        }
        respondAction(js);
        callCore();// process any remaining requests in queue
    }
    function timedOut() {
        // request timed out, take away veil.;
        queue.shift();
        veil.close();
        request.abort();
        respondAction({'error': 'Backend script ' + backEnd + ' timed out after ' + timeOut + ' milliseconds: no responds '});
        callCore();// process any remaining requests in queue
    }

    function setTimeout(n) {
        timeOut = n;
    }
    function setNoQueue(flag) {
        noQueue = flag;
    }
    function useVeil(flag) {
        setVeil = flag;//  true || false
    }
    function fetchHTML(pfid, backend_script, payload, onDoneFunc) {
        'use strict';
        var xxx;
        ////////////////
        // pfid is the id of an html element to fill with content
        // delivered by the backend_script
        // If this html element does yet not exist we create a div
        // to hold the content. 
        ////////////////
        payload.pfid = pfid;
        callDirect(backend_script, payload, getResponds);

        function getResponds(recPkg) {
            var i, dd, ls, nl;
            dd = document.getElementById(recPkg.pfid);
            if (dd === null) {
                return;
            }
            dd.innerHTML = '';
            dd.innerHTML = recPkg.result;
            dd.style.display = 'block';
            ///////////////////
            // here we look for CSS style sheet via link element
            ///////////////////
            ls = dd.getElementsByTagName('LINK');
            nl = ls.length;
            for (i = 0; i < nl; i++) {
                if (ls[i].type === 'text/css' && ls[i].rel === 'stylesheet') {
                    includeCSS(ls[i].href);
                }
            }
            ///////////////////
            // here we look for and execte/load any given JavaScript              
            ///////////////////
            ls = dd.getElementsByTagName('script');
            nl = ls.length;
            GEVAL = eval; //eval in global scope
            for (i = 0; i < nl; i++) {
                if (ls[i].src === 'undefined' || ls[i].src === '') {
                    xxx = GEVAL(ls[i].innerHTML); // executes immediatly in global scope !!!
                } else {
                    includeJS(ls[i].src); // this is evaluated later
                }
            }
            if (typeof onDoneFunc !== 'undefined' && typeof onDoneFunc === 'function') {
                onDoneFunc(recPkg);
            }
        }

        function includeJS(file) {
            var i, heads, script, l = document.getElementsByTagName('SCRIPT');
            for (i = 0; i < l.length; i++) {
                if (l[i].src.indexOf(file) > 0) {
                    return;
                }
            }
            script = document.createElement('script');
            script.setAttribute('type', 'text/javascript');
            script.setAttribute('src', file);
            heads = document.getElementsByTagName('head');
            for (i = 0; i < heads.length; i++) {
                heads[i].appendChild(script);
            }
        }
        function includeCSS(path) {
            var i, link, heads, l = document.getElementsByTagName('LINK');

            for (i = 0; i < l.length; i++) {
                if (l[i].href.indexOf(path) > 0) {
                    return;
                }
            }
            link = document.createElement('link');
            link.setAttribute('rel', 'stylesheet');
            link.setAttribute('type', 'text/css');
            link.setAttribute('href', path);
            heads = document.getElementsByTagName('head');
            for (i = 0; i < heads.length; i++) {
                heads[i].appendChild(link);
            }
        }
    }
    return reveal;
}