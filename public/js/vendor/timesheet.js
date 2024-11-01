!function() {
  /**
   * @param {string} selector
   * @param {number} pos
   * @param {?} options
   * @param {Array} allBindingsAccessor
   * @return {undefined}
   */
  var init = function(selector, pos, options, allBindingsAccessor) {
    /** @type {Array} */
    this.data = [];
    this.year = {
      min : pos,
      max : options
    };
    this.parse(allBindingsAccessor || []);
    if ("undefined" != typeof document) {
      this.container = "string" == typeof selector ? document.querySelector("#" + selector) : selector;
      this.drawSections();
      this.insertData();
    }
  };
  /**
   * @return {undefined}
   */
  init.prototype.insertData = function() {
    /** @type {Array} */
    var tagNameArr = [];
    var x = this.container.querySelector(".scale section").offsetWidth;
    /** @type {number} */
    var n = 0;
    var m = this.data.length;
    for (;m > n;n++) {
      var t = this.data[n];
      var b = this.createBubble(x, this.year.min, t.start, t.end);
      /** @type {string} */
      var bgColorStyle = t.type.indexOf('#') > -1 ? 'background-color:' + t.type + ';' : '';

      var segs = ['<span style="' + bgColorStyle + 'margin-left: ' + b.getStartOffset() + "px; width: " + b.getWidth() + 'px;" class="bubble bubble-' + (t.type || "default") + '" data-duration="' + (t.end ? Math.round((t.end - t.start) / 1E3 / 60 / 60 / 24 / 39) : "") + '"></span>', '<span class="date">' + b.getDateLabel() + "</span> ", '<span class="label">' + t.label + "</span>"].join("");
      tagNameArr.push("<li>" + segs + "</li>");
    }
    this.container.innerHTML += '<ul class="data">' + tagNameArr.join("") + "</ul>";
  };
  /**
   * @return {undefined}
   */
  init.prototype.drawSections = function() {
    /** @type {Array} */
    var tagNameArr = [];
    var temp = this.year.min;
    for (;temp <= this.year.max;temp++) {
      tagNameArr.push("<section>" + temp + "</section>");
    }
    /** @type {string} */
    this.container.className = "timesheet color-scheme-default";
    /** @type {string} */
    this.container.innerHTML = '<div class="scale">' + tagNameArr.join("") + "</div>";
  };
  /**
   * @param {string} date
   * @return {?}
   */
  init.prototype.parseDate = function(date) {
    return-1 === date.indexOf("/") ? (date = new Date(parseInt(date, 10), 0, 1), date.hasMonth = false) : (date = date.split("/"), date = new Date(parseInt(date[1], 10), parseInt(date[0], 10) - 1, 1), date.hasMonth = true), date;
  };
  /**
   * @param {Array} parts
   * @return {undefined}
   */
  init.prototype.parse = function(parts) {
    /** @type {number} */
    var i = 0;
    var l = parts.length;
    for (;l > i;i++) {
      var date = this.parseDate(parts[i][0]);
      var now = 4 === parts[i].length ? this.parseDate(parts[i][1]) : null;
      var lab = 4 === parts[i].length ? parts[i][2] : parts[i][1];
      var paramType = 4 === parts[i].length ? parts[i][3] : 3 === parts[i].length ? parts[i][2] : "default";
      if (date.getFullYear() < this.year.min) {
        this.year.min = date.getFullYear();
      }
      if (now && now.getFullYear() > this.year.max) {
        this.year.max = now.getFullYear();
      } else {
        if (date.getFullYear() > this.year.max) {
          this.year.max = date.getFullYear();
        }
      }
      this.data.push({
        start : date,
        end : now,
        label : lab,
        type : paramType
      });
    }
  };
  /**
   * @param {string} v00
   * @param {string} startColumn
   * @param {string} endRow
   * @param {boolean} endColumn
   * @return {?}
   */
  init.prototype.createBubble = function(v00, startColumn, endRow, endColumn) {
    return new Range(v00, startColumn, endRow, endColumn);
  };
  /**
   * @param {number} endRow
   * @param {number} min
   * @param {Date} from
   * @param {string} to
   * @return {undefined}
   */
  var Range = function(endRow, min, from, to) {
    /** @type {number} */
    this.min = min;
    /** @type {Date} */
    this.start = from;
    /** @type {string} */
    this.end = to;
    /** @type {number} */
    this.widthMonth = endRow;
  };
  /**
   * @param {number} month
   * @return {?}
   */
  Range.prototype.formatMonth = function(month) {
    return month = parseInt(month, 10), month >= 10 ? month : "0" + month;
  };
  /**
   * @return {?}
   */
  Range.prototype.getStartOffset = function() {
    return this.widthMonth / 12 * (12 * (this.start.getFullYear() - this.min) + this.start.getMonth());
  };
  /**
   * @return {?}
   */
  Range.prototype.getFullYears = function() {
    return(this.end && this.end.getFullYear() || this.start.getFullYear()) - this.start.getFullYear();
  };
  /**
   * @return {?}
   */
  Range.prototype.getMonths = function() {
    var getFullYears = this.getFullYears();
    /** @type {number} */
    var months = 0;
    return this.end ? this.end.hasMonth ? (months += this.end.getMonth() + 1, months += 12 - (this.start.hasMonth ? this.start.getMonth() : 0), months += 12 * (getFullYears - 1)) : (months += 12 - (this.start.hasMonth ? this.start.getMonth() : 0), months += 12 * (getFullYears - 1 > 0 ? getFullYears - 1 : 0)) : months += this.start.hasMonth ? 1 : 12, months;
  };
  /**
   * @return {?}
   */
  Range.prototype.getWidth = function() {
    return this.widthMonth / 12 * this.getMonths();
  };
  /**
   * @return {?}
   */
  Range.prototype.getDateLabel = function() {
    return[(this.start.hasMonth ? this.formatMonth(this.start.getMonth() + 1) + "/" : "") + this.start.getFullYear(), this.end ? "-" + ((this.end.hasMonth ? this.formatMonth(this.end.getMonth() + 1) + "/" : "") + this.end.getFullYear()) : ""].join("");
  };
  /** @type {function (string, number, ?, Array): undefined} */
  window.Timesheet = init;
}();
