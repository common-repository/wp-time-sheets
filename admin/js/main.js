;(function ($) {
	"use strict";

  var TS = window.TS || {
  	_views: {},
    _vent : Backbone.Events
  };

  TS._views.TimesheetNewRecordDialogView = Backbone.View.extend({

    events: {
      'submit form#timesheet-new-record-form': 'validate',
    },

    initialize: function(options) {

      _.bindAll(this, 'validate');

      this.options = options || {};

      console.log("View [TimesheetNewRecordDialogView] initialize!");
      this.$form = this.$el.find('form');

      this.$form.validate({
        rules: {
          _tscf_new_start_time: {
            required: true,
          },
          _tscf_new_end_time: {
            required: true,
          },
          _tscf_new_event_or_data: {
            required: true,
            maxlength: 120
          },
          _tscf_new_color: {
            required: true,
            maxlength: 80
          }
        }
      });

      this.$form.find(".wpcolorpicker").wpColorPicker();

      console.log("View [TimesheetNewRecordDialogView] before magnificPopup init!");
      //Edit record/row flag
      this.isEdit = 0;
    },

    open: function(formData, isEdit){

      var self = this;

      //set new flag
      this.isEdit = isEdit;
      this.populateForm(formData);

      $.magnificPopup.open({
        items: {
          src: self.$el
        },
        type: 'inline',
        closeOnBgClick: false,
        preloader: false,
        callbacks: {
          open: function() {
            self.afterPopupOpened();
            console.log("View [TimesheetNewRecordDialogView] open!");
          },
          close: function() {
            self.$form.find(':input').val('');

            self.$form.find(".month-datepicker").each(function(index, el) {
              var dp = $(this).data('datepicker');
              dp.destroy();
            });

            console.log("View [TimesheetNewRecordDialogView] close!");
          }
        }
      });

    },

    afterPopupOpened: function(){

      var minYear = TS.$postForm.find('input[name=_tscf_start_date]').val();
      var maxYear = TS.$postForm.find('input[name=_tscf_end_time]').val();

      this.$form.find(".month-datepicker").each(function(index, el) {

        var dp = $(this).datepicker({
          view: "months",
          minView: "months",
          language: "en",
          dateFormat: "mm/yyyy",
          autoClose: true,
          onRenderCell: function (date, cellType) {

            if(cellType == 'month') {
                var year = date.getFullYear(),
                isDisabled = true;

                if(minYear <= year && year <= maxYear){
                  isDisabled = false;
                }

                return {
                    disabled: isDisabled
                }
            }
          }
        }).data('datepicker');

        var monthYear = $(this).val();

        if(monthYear){
          monthYear = monthYear.split('/');
          var d = new Date();
          d.setMonth(monthYear[0] - 1); //month starts from 0
          d.setFullYear(monthYear[1]);
          dp.selectDate(d);
        }

      });

    },

    populateForm: function(data){

      this.$form.find('input[name=_tscf_new_record_index]').val(data.index);
      this.$form.find('input[name=_tscf_new_start_time]').val(data.start_time);
      this.$form.find('input[name=_tscf_new_end_time]').val(data.end_time);
      this.$form.find('input[name=_tscf_new_event_or_data]').val(data.event_or_data);
      this.$form.find('input[name=_tscf_new_color]').val(data.color);
      this.$form.find(".wpcolorpicker").wpColorPicker('color', data.color);

    },

    validate: function(e){
      e.preventDefault();

      if(this.$form.valid()){
        this.save();
      }
    },

    save: function(){

      var fields = this.$form.serializeArray();
      var rc = {
        index: _.findWhere(fields, {name: "_tscf_new_record_index"}).value,
        start_time: _.findWhere(fields, {name: "_tscf_new_start_time"}).value,
        end_time: _.findWhere(fields, {name: "_tscf_new_end_time"}).value,
        event_or_data: _.findWhere(fields, {name: "_tscf_new_event_or_data"}).value,
        color: _.findWhere(fields, {name: "_tscf_new_color"}).value,
      };

      if(this.isEdit){
        this.options.parent.renderTableRow(rc, 1);
      } else {
        this.options.parent.renderTableRow(rc, 0);
      }
      $.magnificPopup.close();
    }
  });


  TS._views.TimeSheetView = Backbone.View.extend({

    events: {
      "click #add-new-timesheet-record-btn": "addRow",
      "click .remove-item": "removeRow",
    	"click .edit-item": "editRow",
    },

    initialize: function() {

      var self = this;

      this.tmpl = _.template($("#timesheet-records-table-item").html());

      //cache elements
      this.$timesheetRecordsTableBody = this.$el.find("#timesheet-records-table > tbody");
      this.$noRecordsIndicatorRow = this.$timesheetRecordsTableBody.find("tr.no-records-found");

      this.$startDateControl = this.$timesheetRecordsTableBody.find("input[name=_tscf_start_date]");
      this.$endDateControl = this.$timesheetRecordsTableBody.find("input[name=_tscf_end_time]");

      this.$timesheetRecordsTableBody.sortable({
        placeholder: "ui-state-highlight",
        update: function(event, ui) {
          self.reorderRowIndices();
        }
      });

      //init child view
      this.timesheetNewRecordDialogView = new TS._views.TimesheetNewRecordDialogView({
        parent: this,
        el: "#timesheet-new-record-dialog"
      });

    },

    isYearsSelected: function(){
      return TS.$postForm.valid();
    },

    addRow: function(e){
      e.preventDefault();

      if(!this.isYearsSelected()){
        return;
      }

      var newRecordIndex = this.$timesheetRecordsTableBody.find('tr[data-index]').length + 1;

      var rc = {
        index: newRecordIndex,
        start_time: '',
        end_time: '',
        event_or_data: '',
        color: '#2ab515'
      };

    	this.timesheetNewRecordDialogView.open(rc, false);
    },

    renderTableRow: function(rc, isEdit){
      var compiled = this.tmpl({
        rc: rc
      });
      this.$noRecordsIndicatorRow.hide();
      if(isEdit){
        this.$timesheetRecordsTableBody.find('tr[data-index="'+ rc.index +'"]').replaceWith(compiled);
      } else{
        this.$timesheetRecordsTableBody.append(compiled);
      }
      this.refreshSortable();
    },

    editRow: function(e){

      if(!this.isYearsSelected()){
        return;
      }

      var $row = $(e.currentTarget).closest('tr');
      var fields = $row.find(':input').serializeArray();
      var rowIndex = $row.data('index');

      console.log("View [TimeSheetView] editRow method!", fields);

      var rc = {
        index: rowIndex,
        start_time: _.findWhere(fields, {name: "_tscf_record_start_time" + "["+ rowIndex +"]" }).value,
        end_time: _.findWhere(fields, {name: "_tscf_record_end_time" + "["+ rowIndex +"]" }).value,
        event_or_data: _.findWhere(fields, {name: "_tscf_record_event_or_data" + "["+ rowIndex +"]" }).value,
        color: _.findWhere(fields, {name: "_tscf_record_color" + "["+ rowIndex +"]" }).value,
      };

      console.log("View [TimeSheetView] editRow method!", rc);
      this.timesheetNewRecordDialogView.open(rc, true);

    },

    removeRow: function(e){
      e.preventDefault();
      var $target = $(e.currentTarget).closest("tr");
      $target.hide("slow", _.bind(function(){
        $target.remove();
        if(this.$timesheetRecordsTableBody.find("tr").length == 1){
          this.$noRecordsIndicatorRow.show();
        }
        this.refreshSortable();
      }, this));
    },

    reorderRowIndices: function(){
      this.$timesheetRecordsTableBody.find("tr")
        .not(this.$noRecordsIndicatorRow)
        .each(function(index, el) {
          $(el).find("td:first").text(index + 1);
        });
    },

    refreshSortable: function() {
      this.$timesheetRecordsTableBody.sortable("refresh");
      this.reorderRowIndices();
    }

  });

	//DOM ready callback
	$(function() {

    //Cache elements
    TS.$postForm = $('form[name=post]');

    TS.$postForm.find(".year-datepicker").each(function(index, el) {

      var dp = $(this).datepicker({
        view: "years",
        minView: "years",
        language: "en",
        dateFormat: "yyyy",
        autoClose: true,
      }).data('datepicker');

      var year = $(this).val();

      if(year){
        var d = new Date();
        d.setFullYear(year);
        dp.selectDate(d);
      }

    });

    new TS._views.TimeSheetView({
      el: "#timesheets-form"
    });

    //validate post form for few fields
    TS.$postForm.validate({
      rules: {
        "_tscf_timesheet_name": {
          required: true,
          maxlength: 120
        },
        "_tscf_start_date": {
          required: true,
        },
        "_tscf_end_time": {
          required: true,
        },
      }
    });


    //Expose
    window.TS = TS;

	}); //END Ready


}(jQuery));
