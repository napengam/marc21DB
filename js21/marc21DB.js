/* global socket, server, dialogs, backend, util */

backend = myBackend();
dialogs = bulmaDialog();
marc21DB = marc21DBF();
socket = socketWebClient(server, '/web');
window.addEventListener('load', marc21DB.start, false);
function marc21DBF() {

    var sourceid, name, st, uuid = '', allids = [], lastTitleIn = null,searchVal,colname,
            tiCursor = {'max': 200, 'n': 0, 'start': 0, 'end': 200 - 1, 'total': 0, 'ids': []};
    function start() {


        util.mapFunctions(document.body, '[data-funame]', marc21DB);

        // *****************************************
        // sets selector in header
        // ******************************************
        backend.fetchHTML('selectfile', 'classes-GUI/showDNBFiles.php', {}, (resPkg) => {
            if (resPkg.error !== '') {
                dialogs.myInform(resPkg.error);
                return;
            }
            setTimeout(() => { // why ??
                util.mouseEventHere('selector', 'change');
            }, 200);

        });
        // *****************************************
        // init fulltext search
        // ******************************************

        let se = document.getElementById('searchnav').querySelectorAll('input');
        se.forEach((elem) => {
            elem.addEventListener('change', search, false);
        }
        );

        // *****************************************Fpagi
        // set footer with pager
        // ******************************************
        posfoot();
        window.addEventListener('resize', resizeOn, false);
        // *****************************************
        // init websocket 
        // ******************************************
        socket.setCallbackReady(ready);
        socket.setCallbackReadMessage(readMessage);
        socket.setCallbackStatus(sockStatus);
        socket.setCallbackClose(closeSocket);
        socket.init();

    }

    function sockStatus(m) {
//*******************************
// report connection status
//*******************************
        dialogs.myInform(m);
    }
    function closeSocket() {
//*******************************
// report connection status
//*******************************       
    }

    function readMessage(packet) {
//*******************************
// respond to messages from server
//*******************************

        if (packet.opcode === 'broadcast') {
        } else if (packet.opcode === 'feedback') {
            dialogs.myInform(packet.message);
        } else if (packet.opcode === 'echo') {

        }
    }
    function ready() {
// ***********************************************
// we have now the uuid from the server and can start
// ***********************************************
        uuid = socket.uuid();
        dialogs.closeDiag();
    }


    function showTitles(t) {

        let sel = document.getElementById("selector");
        let opt = sel.options[sel.selectedIndex];
        let obj = document.getElementById('dnbfile');
        if (obj) {
            obj.innerHTML = opt.dataset.name;
        }
        tiCursor = {'max': 200, 'n': 0, 'start': 0, 'end': 200 - 1, 'total': 0, 'ids': []};
        pagerOff();
        sourceid = opt.dataset.id;
        name = opt.dataset.name;
        sel.selectedIndex = 0;
        showOnlyTitles(t);
        showDDC();
        lastTitleIn = null;
    }

    function showOnlyTitles(t) {
        let obj = document.getElementById('titles');
        if (obj) {
            obj.innerHTML = '';
            obj.classList.add('is-skeleton');
        }
        dialogs.myInform(name + ' Titel lesen');
        let ddc = '';
        let ddcText = '';
        if (typeof t.dataset.ddc !== 'undefined') {
            ddc = t.dataset.ddc;
            tiCursor.start = 0;
            tiCursor.end = tiCursor.max;
            ddcText = ' / DDC ' + ddc + ', ' + t.cells[2].innerText;
        }
        searchVal = '';
        colname = '';
        if (typeof t.dataset.pattern !== 'undefined' && t.dataset.pattern.trim() !== '') {
            searchVal = t.dataset.pattern;
            colname = t.dataset.colname;
            tiCursor.start = 0;
            tiCursor.end = tiCursor.max;
            t.dataset.pattern = '';
            if (searchVal) {
                let obj = document.getElementById('ddc');
                if (obj) {
                    obj.innerHTML = '';
                }              
            }
        }
        lastTitleIn = null;
        backend.callDirect('classes-GUI/showTitles.php',
                {'id': sourceid, 'ddc': ddc, 'uuid': uuid, 'search': searchVal, 'colname': colname, 'cursor': tiCursor},
                (resPkg) => {

            if (resPkg.error !== '') {
                dialogs.myInform(resPkg.error);
                return;
            }

            let obj = document.getElementById('titles');
            if (obj) {
                obj.innerHTML = resPkg.result;
                obj.style.height = 'fit-content';
                obj.classList.remove('is-skeleton');

            }
            util.mapFunctions(obj, '[data-funame]', marc21DB);

            allids = resPkg.ids;
            //document.body.style.height = (document.body.clientHeight + document.getElementById('pgfoot').clientHeight) + 'px';
            obj.style.height = (obj.clientHeight + document.getElementById('pgfoot').clientHeight) + 'px';
            dialogs.closeDiag();
            window.scrollTo({
                top: 0,
                behavior: 'smooth' // Optional: Add smooth scrolling behavior
            });
            toolTip();
            obj = document.getElementById('filter');
            // *****************************************
            // pager needed ?
            // ******************************************
            if (resPkg.ntitles < allids.length) {
                tiCursor.total = allids.length;
                tiCursor.n = resPkg.ntitles;
                tiCursor.end = tiCursor.start + tiCursor.n - 1;
                if (obj) {
                    obj.innerHTML = `Titel ${tiCursor.start + 1} bis ${tiCursor.end + 1} ${ddcText} `;
                }
                pagerOn();
            } else {
                pagerOff();
                if (obj) {
                    obj.innerHTML = `Titel ${tiCursor.start + 1} bis ${resPkg.ntitles}  ${ddcText}`;
                }
            }
            addShowHideClick();
            if (document.getElementById('ddc').innerHTML === '' && searchVal === '') {
                showDDC();
            }
        });
    }
    function showDDC(param = null) {


        let script, payload, obj;
        obj = document.getElementById('ddc');
        obj.classList.add('is-skeleton');

        script = 'classes-GUI/showDDC.php';
        payload = {'id': sourceid, 'uuid': uuid};

        if (param !== null && 1 === 2) {  // disable path
            script = 'classes-GUI/showSearchDDC.php';
            payload = param;
        }

        backend.callDirect(script, payload, (resPkg) => {
            if (resPkg.error !== '') {
                dialogs.myInform(resPkg.error);
                return;
            }

            let obj = document.getElementById('ddc');
            if (obj) {
                obj.innerHTML = '';
                obj.innerHTML = resPkg.result;
                obj.classList.remove('is-skeleton');

            }
            dialogs.closeDiag();
            let theTable = document.getElementById('ddctable');
            theTable.addEventListener('click', tableClickDDC, false);

            st = sortTable(theTable); // needed by contextmenu
            //********************************************
            //  context menu is created in js
            //********************************************
            let cm = new contextMenuF('', 'ddctable');
            cm.addMenue([// the memnu comes here
                {id: "sortup", icon: '<i class=" fa fa-sort-alpha-down" aria-hidden="true"></i>', label: ' sort up', handler: sortUp},
                {id: "sortdown", icon: '<i class=" fa fa-sort-alpha-up" aria-hidden="true"></i>', label: ' sort down', handler: sortDown}
            ]);
            theTable.oncontextmenu = (e) => { // <== wrapper around contexmenu
                if (e.srcElement.tagName !== 'TD') {
                    return;
                }
                cm.open(e); // open context menue
            };
            // *****************************************
            // make head sticky
            // ******************************************
            ddcSticky();
            ddcMaxHeight();
            let top = document.getElementById('pghead').clientHeight;
            makeSticky('ddctable', {'col': 0, 'loff': 0, 'toff': 0});
        });
    }
    function search(e) {

        this.dataset.pattern = this.value;
        this.dataset.colname = this.id;
        showOnlyTitles(this);
    }



    function showRaw() {
        let titleid = this.dataset.id;
        window.open('classes-GUI/showAllTags.php?titleid=' + titleid, '_blank', 'width=600,height=400');
    }
    function showBookContent() {
        if (this.dataset.what === 'Inhaltstext') {
            window.event.stopPropagation();
            window.event.preventDefault();
            let href = this.href;
            let ti = this.closest('.content').querySelector('.theTitle').textContent;
            window.open('classes-GUI/showBookContent.php?ti=' + ti + '&href=' + href, '_blank', 'width=600,height=400');
        }
    }

    function sortUp(e) {

        let contextMenu = e.target.closest('.contextParent');
        let col = contextMenu.target.cellIndex; // real event source captured when contextmenu was displayed

        if (col === 1) {
            st.setCompareValues((v1, v2) => {
                if (v1 === '' && v2 === '') {
                    return 0;
                } else if (v1 === '') {
                    return 1;
                } else if (v2 === '') {
                    return -1;
                }
                v1 = parseFloat(v1, 10);
                v2 = parseFloat(v2, 10);
                return v1 - v2;
            });
        }
        st.sortCore(col, 1);
    }
    function sortDown(e) {

        let contextMenu = e.target.closest('.contextParent');
        let col = contextMenu.target.cellIndex; // real event source captured when contextmenu was displayed
        if (col === 1) {
            st.setCompareValues((v1, v2) => {
                if (v1 === '' && v2 === '') {
                    return 0;
                } else if (v1 === '') {
                    return -1;
                } else if (v2 === '') {
                    return 1;
                }
                v1 = parseFloat(v1, 10);
                v2 = parseFloat(v2, 10);
                return v1 - v2;
            });
        }
        st.sortCore(col, -1);
    }
// *****************************************
// position footer
// ******************************************

    function posfoot() {
        let foot = document.getElementById('pgfoot');
        foot.style.position = 'fixed';
        foot.style.top = (window.innerHeight - foot.clientHeight) + 'px';
    }
// *****************************************
// pager
// ******************************************

    function pagerOn() {
        let all = document.getElementById('pager').querySelectorAll('#next');
        all.forEach((elem) => {
            elem.classList.remove('is-hidden');
        });
        posfoot();
    }
    function pagerOff() {
        let all = document.getElementById('pager').querySelectorAll('.findme');
        all.forEach((elem) => {
            elem.classList.add('is-hidden');
        });
        posfoot();
    }
    function otherPage() {

        switch (this.id) {
            case 'pfirst':
                tiCursor.start = -1;
                movePagination(-1);
                break;
            case 'pprev':
                document.getElementById('next').classList.remove('is-hidden');
                movePagination(-1);
                break;
            case 'pnext':
                document.getElementById('prev').classList.remove('is-hidden');
                movePagination(+1);
                break;
            case 'plast':
                tiCursor.end = tiCursor.total + 1;
                movePagination(+1);
                break;
            default:

                break;
        }
        backend.callDirect('classes-GUI/showTitlesPaginate.php',
                {'id': sourceid, 'ddc': ddc, 'uuid': uuid, 'cursor': tiCursor,'search': searchVal, 'colname': colname },
                (resPkg) => {
            let obj = document.getElementById('titles');
            if (obj) {
                obj.innerHTML = resPkg.result;
                obj.style.height = 'fit-content';
            }
            obj.style.height = (obj.clientHeight + document.getElementById('pgfoot').clientHeight) + 'px';
            dialogs.closeDiag();
            window.scrollTo({
                top: 0,
                behavior: 'smooth' // Optional: Add smooth scrolling behavior
            });
            toolTip();
            util.mapFunctions(obj, '[data-funame]', marc21DB);
            obj = document.getElementById('filter');
            if (obj) {
                obj.innerHTML = `Titel ${tiCursor.start + 1} bis ${tiCursor.end + 1}`;
            }
            addShowHideClick();
        });
    }

    function movePagination(dir) {
        tiCursor.start = tiCursor.start + tiCursor.max * dir;
        tiCursor.end = tiCursor.end + tiCursor.max * dir;
        if (tiCursor.start <=0 ) {
            tiCursor.start = 0;
            tiCursor.end = Math.min(tiCursor.start + tiCursor.max, tiCursor.total);
            document.getElementById('prev').classList.add('is-hidden');
            document.getElementById('next').classList.remove('is-hidden');
        } else if (tiCursor.end > tiCursor.total) {
            tiCursor.end = tiCursor.total;
            tiCursor.start = tiCursor.end - tiCursor.max + 1;
            document.getElementById('next').classList.add('is-hidden');
            document.getElementById('prev').classList.remove('is-hidden');
        }
        tiCursor.ids = [];
        for (let i = tiCursor.start, j = 0; i < tiCursor.end; i++) {
            tiCursor.ids[j++] = allids[i];
        }
    }

    function iconsShow() {

        let icons;
        icons = this.querySelector(".is-hidden");
        if (icons) {
            icons.classList.remove('is-hidden');
            icons.classList.add('findMe', 'has-text-grey-lighter');
        }
    }
    function iconsHide() {

        let icons;
        icons = this.querySelector(".findMe");
        if (icons.classList.contains('freezeMe')) {
            return;
        }
        icons.classList.add('is-hidden');
        icons.classList.remove('findMe');
    }
    function iconsActive() {
        let icons;
        icons = this.parentNode.querySelector(".freezeMe");
        if (icons !== null && icons !== window.event.srcElement.parentNode) {
            icons.classList.add('is-hidden');
            icons.classList.remove('freezeMe');
        }
        icons = this.querySelector(".findMe");
        icons.classList.add('freezeMe');
        icons.classList.remove('has-text-grey-lighter');
    }
    function addShowHideClick() {

        let boxes = document.getElementById('titles').querySelectorAll('.box');
        boxes.forEach((elem) => {
            elem.addEventListener('mouseover', iconsShow, false);
            elem.addEventListener('mouseout', iconsHide, false);
            elem.addEventListener('click', iconsActive, false);
        });
        return;
    }

    function resizeOn() {
        window.removeEventListener('resize', resizeOn, false);
        document.body.addEventListener('mouseover', resizeOff, false);
    }
    function resizeOff() {
        document.body.removeEventListener('mouseover', resizeOff, false);
        posfoot();
        ddcSticky();
        ddcMaxHeight();
        window.addEventListener('resize', resizeOn, false);
    }

    function tableClickDDC(e) {
        e.stopPropagation();
        if (e.srcElement.tagName !== 'TD') {
            return;
        }
        showOnlyTitles(e.srcElement.parentNode);
    }

    function ddcMaxHeight() {
        let p0 = absPos('ddcover');
        let p1 = absPos('pgfoot');
        let obj = document.getElementById('ddcover');
        obj.style.maxHeight = p1.y - p0.y + 'px';
    }

    function ddcSticky() {
        let hd;
        hd = absPos('ddcover');
        hd.o.style.position = 'sticky';
        hd.o.style.top = hd.y + 'px';

    }
    function absPos(objs, parent) {// return absolute x,y position of obj
        var ob, x, y, obj;
        if (typeof objs === 'string') {
            obj = document.getElementById(objs);
        } else {
            obj = objs;
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
    return {
        'start': start,
        'showTitles': showTitles,
        'showOnlyTitles': showOnlyTitles,
        'showRaw': showRaw,
        'otherPage': otherPage,
        'iconsShow': iconsShow,
        'iconsHide': iconsHide,
        'iconsActive': iconsActive,
        'showBookContent': showBookContent
    };
}

