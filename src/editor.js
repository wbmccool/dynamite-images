(function($){


    $.fn.serializeObject = function(){
        var o = {},
            a = this.serializeArray();

        $.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };

    function cheapTwigSafeParse(string){
        if(typeof string !== "string"){ return ""; }

        var tagRegexString = "{%[^%}]*%}|%%[^%%]*%%|{{[^}}]*}}",
            tagRegex = new RegExp(tagRegexString,"gi"),
            atStart = new RegExp('^('+tagRegexString+")","i"),
            startsWithTag = atStart.test(string),
            tags = string.match(tagRegex)||[],
            strings = string.split(tagRegex),
            tagLn = tags && tags.length||0,
            lastpointer = 0,
            output = '';

        $.each(strings,function(i,v){
            output += encodeURIComponent(v) + (tags[lastpointer]?tags[lastpointer]:'');
            lastpointer++;
        });

        return output;

    }
    function gup(name) {
        name = name.replace(/(\[|\])/g,"\\$1");
        var regex = new RegExp("[\\?&]"+name+"=([^&#]*)"),
            results = regex.exec( window.location.href );
        return ( results === null )?"":results[1];
    }

    function decodeParam(str){
        var obj = {};
        if(str){
            str
                .split('#')[0]//just in case this sneaks in
                .replace('?','')//same
                .replace(/\&amp;/gi,"&")//in case this is a problem
                .replace(/([^=&]+)=([^&]*)/g, function(m, key, value) {
                    value = value.replace(/\+/g,'%20');
                    obj[decodeURIComponent(key)] = decodeURIComponent(value);
                });
        }
        return obj;
    }
    function parseUrl(url){
        var p = document.createElement('a');//create a special DOM node for testing
        p.href=url;//stick a link into it
        p.pathname = p.pathname.replace(/(^\/?)/,"/");//IE fix
        return p;//return the DOM node's native concept of itself, which will expand any relative links into real ones
    }
    var revision = 0,
        timeout,

        $body = $('body'),
        $layerControl = $body.find('#layers'),
        $form = $body.find("form"),
        $output = $body.find('#output'),
        $outputLink = $body.find('#output-link'),
        $fontSelect = $body.find('#font-family'),
        $controlSection = $body.find('#control-section'),
        $preview = $body.find("#preview"),
        $data = $body.find("#data"),
        $text = $body.find("#text"),
        $tsControls = $body.find('.ts-fields'),
        $tsField = $body.find('[name="text-shadow"]'),
        $toControls = $body.find('.to-fields'),
        $toField = $body.find('[name="outline"]'),
        $alertZone = $body.find('.alert-zone'),
        local = gup('local')==="true",
        layertabsHTML = '',
        get = decodeParam(location.search.replace('?','')),
        filetype = get.img && get.img.split('.')[1],
        layers = [],
        layerkey ='last-edited-layer-'+get.img,
        sEditlayer = sessionStorage && parseInt(sessionStorage.getItem(layerkey),10),
        editlayer = sEditlayer||0,
        defaultEditURL = '/edit.html?local=true&img=BSD-logo.png',
        defaults={
            'text-align':'',
            'vertical-align':'',
            'color':'#000000',
            'font-size':'24',
            'letter-spacing':'',
            'top':'0',
            'left':'0',
            'text-transform':'',
            'font-family':'OpenSans-Regular',
            'line-height':'1.5',
            'angle':'0',
            'white-space':'',
            'max-width':'0',
            'text-shadow':'',
            'outline':''
        },
        allowedKeys = _.keys(defaults).concat(['text']),
        mergedFieldDefaults = {
            'ts-left':0,
            'ts-top':0,
            'ts-color':'#000000',
            'ts-opacity':1,
            'to-spread':0,
            'to-color':'#000000',
            'to-opacity':0.4,
        },
        fontsDfd = $.ajax({url:'/ajax/fonts.json'});

    //non-local/or local GAE version will show the upload form
    if ((window.location.hostname === "localhost" || window.location.hostname === "127.0.0.1") && !get.img){
        window.location = defaultEditURL;
    }

    /*
    /* not sure we really care about these this enough to expose it as openly here, taking up UI room
    if(filetype === 'jpg' || filetype === 'jpeg'){
        $form = $("form").append('<div class="form-group"><label for="left">JPG Quality</span></label><input name="jpgquality" min="0" step="10" max="100" type="number" value="100"></div>');
    }

    if(filetype === 'png'){
        $form = $("form").append('<div class="form-group"><label for="left">PNG Compression <span class="small">(speed: 1 vs small filesize: 9)</span></span></label><input name="pngcomp" min="0" step="1" max="9" type="number" value="5"></div>');
    }
    */

    function showUploadForm(){
        $preview.removeClass('loading');
        if(!local){
            $preview.load('/upload.php #upload-form-col', function(resp, status, xhr){
				if(status === "error"){
					$preview.html("No image specified. <p><a href='/upload.php'>Please login as an admin to upload an image</a>.");
				}
            });
        }else{
            $preview.html('No file found. Either add one to your local /img directory or <a href="'+defaultEditURL+'">Edit the default</a>');
        }
    }

    function layerSplit(params){
        var array = [{}],
            highest = 0;

        $.each(params,function(k,v){
            var extractedKey,
                truekey;
            if(k.indexOf(']')===-1){
                array[0][k] = v;
            }else{
                truekey = k.split('[')[0];
                extractedKey = k.split('[')[1].replace(']','');

                if(!extractedKey){
                    extractedKey = array.length;
                }
                if(!array[extractedKey]){
                    array[extractedKey]={};
                }
                if(_.contains(allowedKeys,truekey)){//only allowed keys
                    array[extractedKey][truekey] = v;
                }
            }
        });
        return array;
    }
console.log(allowedKeys);

    function setFormFromParams(startParams){
        var tstemp,
            totemp;

        startParams = _.defaults(startParams,defaults);

        if(startParams.color && startParams.color.indexOf('#')===-1){
            startParams.color = '#'+startParams.color;
        }
        if(startParams['white-space']==="normal"){
            $form.find('[name="max-width"]').closest('.form-group').toggleClass('dim',false);
        }

        /*I think these are not properly clearing between layer switches: we may need a global way to reset defaults*/

            tstemp = (startParams['text-shadow'] && startParams['text-shadow']||'0 0 #000000 0').split(' ');
            $tsControls.find('input').each(function(i,el){
                if(i === 3){
                    tstemp[i] = Math.round((1 - ((Math.round((tstemp[i]/127)*10) ) / 10))*10)/10;
                }
                $(el).val(tstemp[i]);
            });

            totemp = (startParams.outline && startParams.outline||'0 #000000 77').split(' ');
            $toControls.find('input').each(function(i,el){
                if(i === 2){
                    totemp[i] = Math.round((1 - ((Math.round((totemp[i]/127)*10) ) / 10))*10)/10;
                }
                $(el).val(totemp[i]);
            });


        $.each(_.defaults(startParams,defaults),function(k,v){
            $form.find('[name="'+k+'"]').val(v);
            console.log(k,'',v,'=',$form.find('[name="'+k+'"]').val());
        });

        if(startParams.text){
            $text.val(startParams.text);
        }
    }

    function newLayerTab(id,active){
        return (id===0)?
            '<li role="presentation" data-layer="'+id+'" class="layer-tab'+((active===id)?' active':'')+'"><a data-toggle="dropdown" aria-expanded="false">Layer '+(id+1)+'</a></li>':
            '<li role="presentation" data-layer="'+id+'" class="layer-tab dropdown'+((active===id)?' active':'')+'"><a data-toggle="dropdown" aria-expanded="false">Layer '+(id+1)+' <span class="caret"></span></a><ul class="dropdown-menu" role="menu"><li title="Remove this text layer"><a data-delete="'+id+'" class="delete">Delete layer</a></li></ul></li>';
    }

    function updateParamState(){

        var hash = window.location.href.split('#')[1]||'',
            layertabsHTML = '';

        layers = layerSplit(decodeParam(hash));
        editlayer = layers[editlayer] && editlayer||layers.length-1>0 && layers.length-1||0;
        sessionStorage.setItem(layerkey,editlayer);

        //totally fresh defaults
        if(hash===""){
            layers[0].left = 80;
            layers[0].top = 80;
            layers[0]['text-align']='';
        }

        $layerControl.find('.layer-tab').remove();
        $.each(layers,function(i){
            layertabsHTML += newLayerTab(i,editlayer);
        });
        $layerControl.prepend(layertabsHTML);
        $('#new-layer').toggle(layers.length < 7);

        $controlSection.css({height:'auto'});
        $controlSection.height($controlSection.height());

        return layers;
    }

    function warn(msg){
        var htmlstring = '<div class="alert alert-warning alert-dismissible fixed-alert" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Warning!</strong> '+msg+'</div>';
        $alertZone.append(htmlstring);
    }
    window.warn = warn;

    function startup(){

        updateParamState();//get all the layers from the hash state
        setFormFromParams(layers[editlayer]);//set all the form elements to match the first layer

        $form.on('change','[name="vertical-align"]',function(){
            //if centering vertically, reset the Y offset to 0
            var val = $(this).val(),
                $topField = $('[name="top"]'),
                imgHeight = $preview.find('img').data('height');
            if(val==="middle"){
                $topField.val(imgHeight/2);
            }
            else if(val==="bottom"){
                $topField.val(imgHeight);
            }else{
                $topField.val(0);
            }
        }).on('change','[name="text-align"]',function(){
            //if centering horizontally, reset the X offset to 0
            var val = $(this).val(),
            imgWidth = $preview.find('img').data('width');
            if(val==="center"){
                $('[name="left"]').val(Math.abs(imgWidth/2));
            }
            else if(val==="" || val==="right"){
                $('[name="left"]').val(0);
            }
        }).on('change','[name="white-space"]',function(){
            //if centering horizontally, reset the X offset to 0
            var val = $(this).val();
            $form.find('[name="max-width"]').closest('.form-group').toggleClass('dim',val!=="normal");
        }).on("change init navigate", function(e){

            layers[editlayer] = $(this).find('[name]').filter(function(index, element) {
                var $el = $(element),
                    name = $el.attr('name'),
                    val = $el.val();
                return val !== "" && val !==defaults[name];
            }).serializeObject();

            layers = _.map(layers, function(v,i){
                return _.pick(v,function(v,k){
                    return k!=='local' && v !== "" && v !==defaults[k];
                });
            });

            var params = (get.local?'local=1':'') + (layers.length===1?
                    '&'+$.param(_.omit(layers[0],'text')) :
                    _.reduce(layers, function(memo,v,i){
                        return memo +'&'+ ($.param(_.omit(v,'text')).replace(/\+/g,'%20').replace(/\=/g,'['+i+']='));
                    },'')),
                paramsURL = params + (layers.length===1?
                    "&text=" + encodeURIComponent(layers[0].text):
                    _.reduce(layers, function(memo,v,i){
                        return memo + (("&text=" + encodeURIComponent(v.text)).replace(/\=/g,'['+i+']='));
                    },'')),
                paramsUnencoded = params + (layers.length===1?
                    "&text=" + cheapTwigSafeParse(layers[0].text):
                    _.reduce(layers, function(memo,v,i){
                        return memo + (("&text=" + cheapTwigSafeParse(v.text)).replace(/\=/g,'['+i+']='));
                    },'')),
                src = get.img + "?"+ paramsUnencoded,
                timer = _.now(),
                img = new Image();

            $alertZone.empty();
            $preview.addClass('loading');
            img.onerror = showUploadForm;
            img.onload = function(){
                var $img = $(img),
                    data = {
                        height:img.height,
                        width:img.width,
                        generated_in: ((_.now()-timer))+'ms',
                        url_character_length: img.src.length
                    };
                $preview.removeClass('loading').html(img);
                $(img).data(data).attr({'alt':revision,title:'Click to position the text layer absolutely'});
                $data.html(JSON.stringify(data,undefined,1));
                if(data.height>=1700){
                    warn('Images longer than 1700 pixels are not recommended, as they may break in older email clients. Consider breaking your image into multiple images.');
                }
            };
            img.src = src;

            $output.val(parseUrl(src).href.split('?')[0] + "?" + paramsUnencoded);
            $outputLink.attr('href',img.src).css({visibility:'visible',opacity:1});

            if(img.src && img.src.length>=2118){
                warn('The url string you\'ve created is longer than 2117 characters which exceeds what Google App Engine will allow (the image preview is very likely broken).  Consider breaking your image into multiple images.');

            }else if(img.src && img.src.length>=1900){
                warn('The url string you\'ve created is longer than 1900 characters which may start to break in older email clients. Consider breaking your image into multiple images.');
            }
            revision++;

            if(e.type!=="navigate"){
                history.pushState({id:revision,layers:layers.length},revision,window.location.href.split('#')[0]+'#'+paramsURL);
            }

        }).trigger("init");

        $text.on('keyup',function(){

            //every time the user types, wait 800ms and if they haven't already triggered a change, update
            var oldrevision = revision;
            clearTimeout(timeout);
            timeout = setTimeout(function(){
                if(revision === oldrevision){
                    $text.trigger('change');
                }
            },800);
        });

        $output.on('focus',function(){
            var $el = $(this);
            window.setTimeout (function(){
                $el.select();
            },20);
        });//select all text in copy/paste target

        $preview.on('click','img',function(e){
            //if setting text position with a click, undo alignments first, then set offsets
            var offset = $(this).offset(),
                $img = $preview.find('img'),
                vd = {//virtual dimensions
                    width:$img.width(),
                    height:$img.height()
                },
                rd = $img.data(),//real dimensions
                left = e.pageX - offset.left,
                top = e.pageY - offset.top,
                alignment = $form.find('[name="text-align"]').val();

            if(vd.width !== rd.width){
                left = left * (rd.width/vd.width);
            }
            if(vd.height!== rd.height){
                top = top * (rd.height/vd.height);
            }

            if(alignment==="right"){
                left = rd.width - left;
            }
            else if(alignment==="center"){
                //$form.find('[name="text-align"]').val('').end();
            }

            $form
                //.find('[name="text-align"]').val('').end()
                //.find('[name="vertical-align"]').val('').end()
                .find('[name="left"]').val(Math.round(left)).end()
                .find('[name="top"]').val(Math.round(top))
                .trigger('change');
        });

        $tsControls.on('change',function(){
            var fullVal = $tsControls.find('input').map(function(){
                var $el = $(this);
                return $el.is('#ts-alpha')?127-(~~($el.val()*127)):$el.val();
            }).get().join(' ');

            if(($('#ts-left').val()==="0" && $('#ts-top').val()==="0") || $('#ts-alpha').val()==="0"){
                $tsField.val('');
            }else{
                $tsField.val(fullVal);
            }
            $form.trigger('change');
        });

        $toControls.on('change',function(){
            var fullVal = $toControls.find('input').map(function(){
                    var $el = $(this);
                    return $el.is('#to-alpha')?127-(~~($el.val()*127)):$el.val();
                }).get().join(' ');

            if($('#to-spread').val()==="0" || $('#to-alpha').val()==="0"){
                $toField.val('');
            }else{
                $toField.val(fullVal);
            }
            $form.trigger('change');
        });

        //enable a particular layer
        $layerControl.on('click','[data-layer]',function(){
            $layerControl.find('.active').removeClass('active');
            var $el = $(this).addClass('active');
            editlayer = parseFloat($el.data('layer'));
            sessionStorage.setItem(layerkey,editlayer);
            setFormFromParams(layers[editlayer]);
        });


        //create a new layer
        $body.on('click','#new-layer',function(){

            var oldlayer = _.clone(layers[editlayer]),
                newlayer = $.extend(
                    true,
                    oldlayer||{},
                    {
                        left:parseFloat(oldlayer.left||0)+5,
                        top:parseFloat(oldlayer.top||0)+5
                    }
                ),
                $newLayer;

            layers.push( _.pick(newlayer,_.keys(defaults)) );
            editlayer = layers.length-1;

            $newLayer = $(newLayerTab(editlayer));

            $(this).before($newLayer);
            $newLayer.click();
            if(layers.length === 8){
                $('#new-layer').hide();
            }
            $form.trigger('change');
            //setFormFromParams(layers[editlayer]);
        }).on('click','#reset',function(){
            var reset = confirm("Are you sure you want to start from scratch?");
            if(reset) { window.location = location.href.split('#')[0]; }
        }).on('click','[data-delete]',function(){
            var $el = $(this),
            which = $el.data('delete');

            if(which===editlayer){
                editlayer = editlayer-1;
            }

            layers.splice($el.data('delete'),1);
            $layerControl.find('.layer-tab').eq(editlayer).click();
            $form.trigger('change');
            updateParamState();
        }).on('click','.clearer',function(){
            $(this).prevAll('input').each(function(){
                var $el = $(this),
                name = $el.attr('name');

                if(name in defaults){
                    $el.val(
                        name==="top" && layers[editlayer]['vertical-align'] && layers[editlayer]['vertical-align']!==''?
                            ($preview.find('img').data('height') / (layers[editlayer]['vertical-align']==="bottom"?1:2)):
                            name==="left" && layers[editlayer]['text-align'] && layers[editlayer]['text-align']==='center'?
                                Math.round($preview.find('img').data('width')/2):
                                defaults[name]
                    );
                }

                else if(name in mergedFieldDefaults){
                    $el.val(mergedFieldDefaults[name]);
                }
            });
            $(this).prev('input').trigger('change');
        });

        //position sticky panel needs an explicit height to work right with flexbox, so listen for textarea resized and correct if possible
        $controlSection.on('mousemove','textarea',_.debounce(function(){
            $controlSection.css({height:'auto'});
            $controlSection.height($controlSection.height());
        },50));

        //if the user navigates, set everything to the new state
        $(window).on('hashchange', function(){
            updateParamState();
            editlayer = 0;
            setFormFromParams(layers[0]);
            $form.trigger('navigate');
        });

        $controlSection.addClass('loaded');
    }//startup

    fontsDfd.done(function(fonts){
        var options='';
        $.each(fonts,function(i,font){
            options += '<option value="'+font+'"'+(font==='OpenSans-Regular'?'selected':'')+'>'+font+'</option>';
        });
        $fontSelect.html(options);
    },startup);


}(jQuery));