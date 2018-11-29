function JsXzlib_NovaPoshta() {
    let $ = jQuery ;

    this.Noty = {
        showLogs: (logs) => {
            let arrLogdKey = {
                error: 'errors',
                warning: 'warnings',
                info: 'info'
            };
            $.each(arrLogdKey, (i, ind) => {
                if (logs[ind].length > 0) {
                    $.each(logs[ind], (im, mes) => {
                        new Noty({
                            type: i,
                            layout: 'bottomLeft',
                            text: mes,
                            timeout: 3500,
                        }).show();
                    })
                }


            });
        },
    };


    this.Setting = {
        Warehouses:{
            selector: '.element_Warehouse',
            placeholder:'Выбирите отделение...',
        },
        Chosen:{
            disable_search_threshold: 10,
            no_results_text: "Не найдено!!!",
            select_all_text             :   "BBB",
            select_some_options_text    :    "AAA",
        },
        objDef : (()=>{
            let def = {
                v: 2,
                component: 'api',
                api: 'nova_poshta',
                opt: {},
                method_id:$('[name="method_id"]').val(),
                nova_poshta:'objDef objDef objDef objDef ',
            };
            return $.extend(true, JsXzlib_NovaPoshta.prototype.Setting.objDef ,def )
        })(),
        optPreload : {
            action: 'objEfect',
            blockPreloader: $('body'),
            style: 'roll',
        },
        autocomplete:{
            element:false ,
            streetType:false ,
            RefStreet: false , // #nova_pochta_RefStreet
            query : {
                model:'Address',
                task: 'getStreet' /*'searchSettlementStreets' */,

            }
        }
    };
    this.autocompleteAdressInit=function () {
        let NP = new JsXzlib_NovaPoshta();
        let Setting = this.Setting.autocomplete ;

        $(this.Setting.autocomplete.element).autocomplete({
            minLength: 2 ,
            select: function( event, ui ) {
            $(Setting.streetType).val(ui.item.StreetsType);
                $(Setting.RefStreet).val(ui.item.id);



            },
            source: (request, response)=>{
                let dataOptionCity = NovaPoshta.getDataOptionCity( $('#CityRecipient').find('option:selected') );


                let local_StreetsType = $(this.Setting.autocomplete.streetType).val();
                let textEnter = request.term.replace(local_StreetsType ,"").trim();

                NP.Setting.autocomplete.query.opt ={
                    StreetName: textEnter,
                    objCity:dataOptionCity,
                };
                let q = $.extend(true ,  this.Setting.objDef , NP.Setting.autocomplete.query ) ;

                NP.Ajax.Helper.send(q).then((r)=>{
                    response($.map(r.data.data, function(item){

                        var t = (item.StreetsType + " " + item.Description).trim() ;
                        return{
                            StreetsType:item.StreetsType,
                            label: t ,
                            id: item.Ref ,
                            value: t ,
                        }
                    }));
                });


                console.log( q)
                console.log(NP.Setting.autocomplete.query)

            },
        }).autocomplete( "widget" ).addClass( "stritAutocomplete" );

    };

    this.checDataType = function (test_obj) { };


    this.autocomplete = function () {  }


}


JsXzlib_NovaPoshta.prototype = new JsXzlib() ;






















"undefined"===typeof NovaPoshta&&(NovaPoshta={});
"undefined"===typeof NovaPoshta.Setting&&(NovaPoshta.Setting={});
"undefined"===typeof NovaPoshta.Helper&&(NovaPoshta.Helper={});

NovaPoshta.Setting={
    Warehouses:{
        selector: '.element_Warehouse',
        placeholder:'Выбирите отделение...',
    },
    Chosen:{
        disable_search_threshold: 10,
        no_results_text: "Не найдено!!!",
        select_all_text             :   "BBB",
        select_some_options_text    :    "AAA",
    }
};
/**
 * Список информационных полей для городов
 */
NovaPoshta.Setting.dataOptionFieldsCity = [
    'Ref',
    'CityID',
    'Conglomerates',
    'Delivery1',
    'Delivery2',
    'Delivery3',
    'Delivery4',
    'Delivery5',
    'Delivery6',
    'Delivery7',
    'Description',
    'DescriptionRu',
    'IsBranch',
    'SettlementType',
    'SettlementTypeDescription',
    'SettlementTypeDescriptionRu',
    'SpecialCashCheck',
];
/**
 * Список информационных полей для складов
 */
NovaPoshta.Setting.dataOptionFieldsWarehouses = [
    'BicycleParking',
    'CityDescription',
    'CityDescriptionRu',
    'CityRef',
    'Delivery',
    'Description',
    'DescriptionRu',
    'DistrictCode',
    'InternationalShipping',
    'Latitude',
    'Longitude',
    'Number',
    'POSTerminal',
    'PaymentAccess',
    'Phone',
    'PlaceMaxWeightAllowed',
    'PostFinance',
    'Reception',
    'Ref',
    'Schedule',
    'ShortAddress',
    'ShortAddressRu',
    'SiteKey',
    'TotalMaxWeightAllowed',
    'TypeOfWarehouse',
    'WarehouseStatus',
];
/**
 * Получить параметры выбранного города
 * @param option
 * @returns {}
 */
NovaPoshta.getDataOptionCity = function(option) {

   //  if ("undefined"===typeof option.data('Ref') || "undefined"===typeof option.data('Ref') )return false ;
    var $ = jQuery ;
    if ( option.attr('value').length === 0  ) return false ;
    var ret = {};
    $.map( NovaPoshta.Setting.dataOptionFieldsCity , (i,e)=>{

        var r = $(option).data(i) ;

        if ( !r ) {
            r = $(option).data(i.toLowerCase()) ;
        }
        ret[i] = r  ;
    }) ;



    return ret ;
};
/**
 *  Создать option для селекта города
 * @param Arr
 * @param opt obj {
 *     firstEmpty: false , // Добавить первый пустой элемент
 * }
 * @returns {Array}
 */
NovaPoshta.getCityOptions =  (Arr , opt )=>{
    var $ = jQuery ;
    var resHtmlOpt = []  ;

    var optDef = {
        firstEmpty: false ,
    };
    var _opt = $.extend(optDef, opt);

    if (_opt.firstEmpty) { resHtmlOpt.push ( NovaPoshta.Helper.getEmptyOption('Вибирете город') ) ; }


    $.each(Arr,(i,a)=>{
        var o = $('<option />' ,{ value : a.Ref, text : a.Description });
        $.map( NovaPoshta.Setting.dataOptionFieldsCity , (index , e )=>{

            console.log(index)
            console.log(e)

            return $(o).data( index , a[index] );
        }) ;
        resHtmlOpt.push (o) ;
    });

    return resHtmlOpt ;
};
/**
 * Создать массив options со складами +  add data info Warehouse
 * @param Arr
 * @param opt
 * @return {array} - jQuery elemetns
 */
NovaPoshta.getWarehousesOptions =  (Arr , opt )=>{
    var $ = jQuery ;
    var resHtmlOpt = []  ;

    var optDef = {
        firstEmpty: false ,
        firstEmptyText : 'Вибирете склад.' ,
    };

    var _opt = $.extend(optDef, opt);

    if (_opt.firstEmpty) { resHtmlOpt.push (NovaPoshta.Helper.getEmptyOption(_opt.firstEmptyText)) }
    $.each(Arr,(i,a)=>{
        var o = $('<option />' ,{ value : a.Ref, text : a.Description });
        $.map( NovaPoshta.Setting.dataOptionFieldsWarehouses , (index,e)=>{
            return $(o).data( index , a[index] );
        }) ;
        resHtmlOpt.push (o) ;
    });
    return resHtmlOpt ;
};
/**
 * Загрузить список городов
 * @param data
 * @param Callback
 */
NovaPoshta.getCity = ( data , Callback)=>{
    var obj = {
        model:'Address',
        task: 'getCity' ,
    };
    NovaPoshta.Helper.objEfect( obj , Callback   );
};
/**
 * Запрос - поиск улиц в справочнике населенных пунктов
 * @param dataCity - объект селект города
 * @param Callback
 */
NovaPoshta.searchSettlementStreets = ( text , dataCity , Callback )=>{
    var obj = {
        model:'Address',
        task: 'getStreet' /*'searchSettlementStreets' */,
        opt:{
            StreetName: text ,
            objCity:dataCity,
            methodId: jQuery('[name="virtuemart_shipmentmethod_id"]:checked').val(),
        },
    };
    NovaPoshta.Helper.objEfect( obj , Callback  , { blockPreloader: false  } );
};
/**
 * Загрузить все данные контрагента
 * @param obj
 * @param Callback
 */
NovaPoshta.getCounterpartiesAllData = ( obj , Callback)=>{
    var objDef = {
        model:'Counterparty',
        task: 'getCounterpartiesAllData' ,
        opt:{
            CounterpartyProperty:"Sender",
        },
    };
    var _obj = jQuery.extend( true, objDef, obj);
    NovaPoshta.Helper.objEfect( _obj , Callback   );
};
/**
 * Получить контрагента
 * @param obj
 * @param Callback
 */
NovaPoshta.getCounterparties = ( obj , Callback)=>{
    var objDef = {
        model:'Counterparty',
        task: 'getCounterparties' ,
        opt:{
            CounterpartyProperty:"Sender",
        },
    };
    var _obj = jQuery.extend( true, objDef, obj);
    NovaPoshta.Helper.objEfect( _obj , Callback   );
};
/**
 *
 * @type {{CitySender: NovaPoshta.On.CitySender}}
 */
NovaPoshta.On={
    selectCity: (evt)=>{
        var $ = jQuery ;
        var dataOptionCity = NovaPoshta.getDataOptionCity( $( evt.target ).find('option:selected') );
        if (dataOptionCity) {
            // Отправить запрос для получения складов в выбранном городе
            NovaPoshta.Warehouses.get(dataOptionCity).then( NovaPoshta.Warehouses.render  );
        }//END IF
    }

};

NovaPoshta.Warehouses = {
    /**
     * Получить список складов в городе.
     * @param dataCity
     * @return {*}
     */
    get: (dataCity) => {
        var obj = {
            model: 'Address',
            task: 'getWarehouses',
            opt: {
                objCity: dataCity,
            },
        };
        return NovaPoshta.Ajax.objSend(obj);
    },
    /**
     * Обноаить селект с городами
     * @param r
     */
    render:(r)=>{
        var $=jQuery ;
        var _fe = false ;
        if (r.data.info.totalCount > 1 )  _fe = true ;
        var opts = NovaPoshta.getWarehousesOptions( r.data.data , { firstEmpty : _fe , firstEmptyText : '*' } );
        $(NovaPoshta.Setting.Warehouses.selector).empty().append(opts).trigger("liszt:updated");
    },
    /**
     * Инит списка складов
     */
    init :()=>{
        jQuery(NovaPoshta.Setting.Warehouses.selector) .attr('data-placeholder' , NovaPoshta.Setting.Warehouses.placeholder ).chosen(NovaPoshta.Setting.Chosen).trigger("liszt:updated");
    },



};


NovaPoshta.Ajax = {
    objSend: (obj , opt ) => {
        var $ = jQuery;
        var optDef = {
            action: 'objEfect',
            blockPreloader: $('body'),
            style: 'roll',
        };

        var objDef = {
            v: 2,
            component: 'api',
            api: 'nova_poshta',
            opt: {
                keyAPI: NovaPoshta.Setting.keyAPI,
                methodId: NovaPoshta.Setting.methodId,
            },
        };
        var _obj = $.extend(true, objDef, obj);
        var o = $.extend(true, optDef, opt);
        return Xzlib.Ajax.send( _obj ,  o );
    },
};
//////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Запрос - Получить список складов в городе
 * @param dataCity
 * @param Callback
 */
NovaPoshta.getWarehouses = ( dataCity , id, Callback )=>{
    var $= jQuery ;
    var obj = {
        model:'Address',
        task: 'getWarehouses' ,
        virtuemart_shipmentmethod_id:id,
        opt:{
            objCity:dataCity,
        },
    };
    NovaPoshta.Helper.objEfect( obj , Callback   );
};



/**
 *
 * @type {{checkResponse: (function(*): *), getEmptyOption: (function(*=): *)}}
 */
NovaPoshta.Helper = {

    /**
     * Отправка запроса (obj) на сервер
     * @param obj
     * @param Callback
     * @param opt
     *
     */
    objSend: (obj, Callback , opt) => {
        var $=jQuery ;
        var optDef = {
            action: 'objEfect',
            blockPreloader:$('body'),
            style: 'roll',
        };



        var objDef = {
            v: 2,
            component: 'api',
            api: 'nova_poshta',
            opt: {
                keyAPI: NovaPoshta.Setting.keyAPI,
                methodId : NovaPoshta.Setting.methodId ,
            },
        };
        var _obj = jQuery.extend( true, objDef, obj );
        var o = $.extend(optDef, opt);



        Xzlib.Helper.objEfect(_obj, Callback , o );
    },



    /**
     * Отправка запроса (obj) на сервер
     * @param obj
     * @param Callback
     * @param opt
     *
     */
    objEfect: (obj, Callback , opt) => {
        var $=jQuery ;
        var optDef = {
            action: 'objEfect',
            blockPreloader:$('body'),
            style: 'roll',
        };



        var objDef = {
            v: 2,
            component: 'api',
            api: 'nova_poshta',
            opt: {
                keyAPI: NovaPoshta.Setting.keyAPI,
                methodId : NovaPoshta.Setting.methodId ,
            },
        };
        var _obj = jQuery.extend( true, objDef, obj );
        var o = $.extend(optDef, opt);



        Xzlib.Helper.objEfect(_obj, Callback , o );
    },


    /**
     * Пред обработка ответа сервера
     * @param r
     * @returns {boolean}
     */
    checkResponse: (r) => {
        var $ = jQuery;
        if (!r.success) {
            var _alert = '';
            if (r.messages.message.length > 0) {

                $.each(r.messages.message, (i, m) => {
                    _alert += m;
                });
            }
            if (r.message) {
                alert(r.message + ' ' + _alert)
            }
        }
        console.log(r.data.data.length)
        return r.data.data.length ;
       // return r.data.length;
    },
    /**
     * Создать пустой элемент <option>
     * @param t
     * @returns {jQuery.fn.init|b.fn.init|jQuery|HTMLElement}
     */
    getEmptyOption: (t) => {
        if (t.length == 0) t = 'Выбрать...';
        if (t == '*') t = '';
        return jQuery('<option />', {
            text: t,
            class: 'emptyOption',
        });
    },
};


































