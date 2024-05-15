function bulmaDialog(language = 'en') {

    if (document.getElementById('bulmaDialog') !== null) {
        //********************************************
        //  we allready exist
        //*******************************************
        return document.getElementById('bulmaDialog').self;
    }
    var lt, dialog, diag, aDiv = document.createElement('DIV'), funcs = [], beforeCloseHook = null,
            handle, zIndex, touch = 'ontouchstart' in window,
            ltext = {
                de: {
                    alert: 'Alarm',
                    confirm: 'Bestätigung',
                    prompt: 'Eingabe',
                    info: 'Information',
                    yes: 'Ja',
                    no: 'Nein',
                    cancel: 'Abbruch',
                    save: 'Sichern',
                    value: 'Wert',
                    close: 'Schliesen',
                    name: 'Name',
                    ok: 'Ok',
                    passwd: 'Passwort',
                    login: 'Anmelden'
                },
                en: {
                    alert: 'Alert',
                    confirm: 'Confirmation',
                    prompt: 'Prompt',
                    info: 'Information',
                    yes: 'Yes',
                    no: 'No',
                    cancel: 'Abort',
                    save: 'Save',
                    value: 'Value',
                    close: 'Close',
                    name: 'Name',
                    ok: 'Ok',
                    passwd: 'Password',
                    login: 'Login'
                }
            };

    if (language === 'de' || language === 'en') {
        lt = ltext[language];
    } else {
        lt = ltext['de'];
    }
    zIndex = heighestZIndex();
    dialog =
            `<dialog id='bulmaDialog' class='outerDialog' style='z-index:${zIndex};position:fixed;width:fit_content;height:fit-content'>
    <div class="message">
        <div class="p-1 is-size-7 message-header has-background-grey-light diagDrag">
            <span id=msghead>No text given</span>
            <button class="delete" aria-label="delete" title=${lt.close}></button>
        </div>
        <div class="message-body" style="overflow:auto;max-width:400px;max-height:600px">
            numquam Lorem ipsum dolor sit amet, consectetur adipisicing elit. Unde, aliquam?
        </div>
    </div>
    <div id='diaghide' style='display:block'>
        <div class="field">
            <label class="label">l1</label>
            <div class="control">
                <input class="input" name=in1 type='text' size='40' maxlength='40'></input>
            </div>
        </div>
        <div class="field">
            <label class="label">l2</label>
            <div class="control">
                <input class="input pw" name=in2 type='text' size='40' maxlength='40'></input>
            </div>
        </div>
        <p>&nbsp;</p>
        <div class="level">
            <div class="level-item">
                <button class='button'>b1</button>
            </div>
            <div class="level-item">
                <button class='button'>b2</button>
            </div>
            <div class="level-item">
                <button class='button'>b3</button>
            </div>
        </div>
    </div>
    <div id='bulmaDialogUpload'>
        <form enctype="multipart/form-data" target='upstat' action=# method="POST">
            <div style='text-align:left;'>
                <input type='hidden' name='MAX_FILE_SIZE' value="100485760">
                <input type=file id='idfile' name='uploadedfile[]' multiple><p>
                <hr>
            </div>
            <input type=submit class='Yes' name=submit value='Save'>
        </form>
        <iframe name=upstat src='' style='max-height:100px;border:0px solid #fff;'></iframe>
    </div>
</dialog>
`;

    makeStyle();
    aDiv.innerHTML = dialog;
    document.body.appendChild(aDiv);
    diag = aDiv.firstChild;

    // *****************************************
    // set up dragging
    // ******************************************
    handle = diag.querySelector('.message-header');
    handle.ontouchstart = handle.onmousedown = dragStart;
    diag.ontouchmove = dragMove;
    diag.ontouchend = diag.ondragend = dragEnd;


    var action = 'click';
    if (touch) {
        action = 'touchstart';
    }
    diag.querySelector('.delete').addEventListener(action, closeDiag, true);

    function myInform(text) {
        resetDialog();
        setHeadMessage(lt.info, text);
        setButton([{label: lt.ok, func: closeDiag}]);
        positionDialogShow(diag, false);
    }
    function myAlert(text, hook = null) {
        resetDialog();
        setHeadMessage(lt.alert, text);
        setButton([{label: lt.ok, func: () => {
                    closeDiag();
                }}]);
        positionDialogShow(diag);
        if (typeof hook === 'function') {
            beforeCloseHook = hook;
    }
    }
    function myConfirm(text, yes, no) {
        resetDialog();
        setHeadMessage(lt.confirm, text);
        setButton([{label: lt.yes, func: yes}, {label: lt.no, func: no, focus: ''}]);
        positionDialogShow(diag);
    }
    function myPrompt(text, value, save, no) {
        resetDialog();
        setHeadMessage(lt.prompt, text);
        setInput([{label: lt.value, value: value}]);
        setButton([{label: lt.save, func: click}, {label: lt.cancel, func: no, focus: ''}]);
        function click() {
            save(diag.querySelector('[name=in1]').value);
        }
        positionDialogShow(diag);
    }
    function myLogin(text, save, no) {
        resetDialog();
        setHeadMessage(text, '');
        setInput([{label: lt.name, value: ''}, {label: lt.passwd, value: ''}]);
        setButton([{label: lt.login, func: click, focus: ''}, {label: lt.cancel, func: no}]);
        let pw = diag.querySelector('[name=in2]');
        pw.type = 'password';
        function click() {
            let name = (diag.querySelector('[name=in1]').value);
            let pwd = (diag.querySelector('[name=in2]').value);
            let pw = diag.querySelector('[name=in2]');
            pw.type = 'text';
            pw.value = '';
            save(name, pwd);
        }
        positionDialogShow(diag);
    }
    function myUpload(text, actionUrl, hiddenFields = '') {
        var f;
        resetDialog();
        setHeadMessage(text, '');
        diag.querySelector('#bulmaDialogUpload').style.display = 'block';
        diag.querySelector('#diaghide').style.display = 'none';
        diag.querySelector('FORM').action = actionUrl;
        f = diag.querySelector('FORM');
        f.idfile.value = '';
        if (hiddenFields !== '') {
            for (let fname in hiddenFields) {
                let inp = f.querySelector(`[name=${fname}]`);
                if (inp) {
                    inp.remove();
                }
                inp = document.createElement('INPUT');
                inp.type = 'hidden';
                inp.name = fname;
                inp.value = hiddenFields[fname];
                f.appendChild(inp);
            }
        }
        diag.querySelector('IFRAME').src = '';
        positionDialogShow(diag);
    }

    function setButton(options) {
        var bu = diag.querySelectorAll('.button');
        options.forEach((elem, i) => {
            bu[i].parentNode.classList.remove('is-hidden');
            bu[i].classList.remove('is-hidden');
            bu[i].innerHTML = elem.label;
            // *****************************************
            // keep track of function signatures for
            // later removal in resetDialog()
            // ******************************************
            funcs[i] = {'bu': bu[i], 'func': elem.func};
            bu[i].addEventListener(action, elem.func);
            bu[i].removeEventListener(action, closeDiag, true);
            bu[i].addEventListener(action, closeDiag, true);
            bu[i].parentNode.classList.remove('is-focused');
            if (typeof elem.focus !== 'undefined') {
                bu[i].classList.add('is-focused');
            }
        });
    }
    function setInput(options) {
        var inp = diag.querySelectorAll('.input');
        options.forEach((elem, i) => {
            inp[i].classList.remove('is-hidden');
            inp[i].value = elem.value;
        });
        inp = diag.querySelectorAll('.label');
        options.forEach((elem, i) => {
            inp[i].classList.remove('is-hidden');
            inp[i].innerHTML = elem.label;
        });
    }
    function setHeadMessage(head, message) {
        diag.querySelector('#msghead').innerHTML = head + '&nbsp&nbsp';
        diag.querySelector('.message-body').innerHTML = message;
    }
    function resetDialog() {
        diag.close();

        document.querySelector('#bulmaDialog').style.width = 'fit-content';
        document.querySelector('#bulmaDialog').style.height = 'fit-content';

        diag.querySelector('#bulmaDialogUpload').style.display = 'none';
        diag.querySelector('#diaghide').style.display = 'block';
        diag.querySelectorAll('.level-item, .label , .input, .button').forEach((elem) => {
            elem.classList.remove('is-focused');
            elem.classList.remove('is-hidden');
            elem.classList.add('is-hidden');
        });
        let pw = diag.querySelector('[name=in2]');
        pw.type = 'text';
        pw.value = '';
        funcs.forEach((elem) => { // clear any listener
            elem.bu.removeEventListener('click', elem.func);
        });
    }
    function positionDialogShow(obj, modal = true) {
        modal ? obj.showModal() : obj.show();
        obj.style.top = (window.innerHeight / 2 - obj.clientHeight / 2) + 'px';
        obj.style.left = (window.innerWidth / 2 - obj.clientWidth / 2) + 'px';
    }

    function closeDiag() {
        event.preventDefault();
        let pw = diag.querySelector('[name=in2]');
        pw.type = 'text';
        if (typeof beforeCloseHook === 'function') {
            beforeCloseHook();
            beforeCloseHook = null;
        }
        diag.close();
    }

    function dragStart(e) {
        let obj = e.srcElement.closest('DIALOG');
        obj.draggable = true;
        if (e.type === 'touchstart') {
            e.preventDefault();
            obj.hgsX = e.changedTouches[0].screenX;
            obj.hgsY = e.changedTouches[0].screenY;
        } else {
            obj.hgsX = e.screenX;
            obj.hgsY = e.screenY;
        }
        obj.hgsposX = obj.offsetLeft;
        obj.hgsposY = obj.offsetTop;
    }
    function dragMove(e) {
        let obj = e.srcElement.closest('DIALOG');
        if (obj.draggable) {
            obj.style.top = obj.hgsposY + (e.changedTouches[0].screenY - obj.hgsY) + 'px';
            obj.style.left = obj.hgsposX + (e.changedTouches[0].screenX - obj.hgsX) + 'px';
        }
    }
    function dragEnd(e) {
        let obj = e.srcElement.closest('DIALOG');
        e.preventDefault();
        if (obj.draggable) {
            let top, left;
            obj.draggable = false;
            if (e.type === 'touchend') {
                top = parseInt(obj.style.top, 10);
                left = parseInt(obj.style.left, 10);
            } else {
                top = e.target.offsetTop + (e.screenY - obj.hgsY);
                left = e.target.offsetLeft + (e.screenX - obj.hgsX);
            }
            // *****************************************
            // make shure dialog is fully visible
            // ******************************************
            top = top < 0 ? 0 : top;
            top = top > window.innerHeight - obj.clientHeight ? window.innerHeight - obj.clientHeight : top;
            left = left < 0 ? 0 : left;
            left = left > window.innerWidth - obj.clientWidth ? window.innerWidth - obj.clientWidth : left;
            obj.style.top = top + 'px';
            obj.style.left = left + 'px';
            obj.style.position = 'fixed';
        }
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
    function makeStyle() {
        var styleElem = document.createElement('STYLE');
        styleElem.innerHTML = [
            `.outerDialog{
                overflow:auto;
                resize:both;
                min-height:10px;
                position:fixed;
                margin:0;
                border:0px solid black;
                max-width:600px;
                min-width:150px;
                box-shadow: 1px 2px 9px #677c8a;
             }`,
            `dialog::backdrop{
                overflow:auto;
                resize:both;
                min-height:10px;
                opacity:0;
                background:red;
                position:fixed;
                top:0px;
                right:0px;
                bottom:0px;
                left:0px;
            }`,
            `.diagDrag{
                  cursor:grab
            }`
        ].join('');
        document.getElementsByTagName('head')[0].appendChild(styleElem);
        return styleElem;
    }
    diag.self = {
        myAlert: myAlert,
        myConfirm: myConfirm,
        myPrompt: myPrompt,
        myInform: myInform,
        myLogin: myLogin,
        myUpload: myUpload,
        closeDiag: closeDiag
    };
    return diag.self;
}
