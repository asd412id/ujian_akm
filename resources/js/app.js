require('./bootstrap');

import Alpine from 'alpinejs';
import LeaderLine from 'leader-line-new';
import { Html5Qrcode } from "html5-qrcode";
import MD5 from 'crypto-js/md5';

window.Alpine = Alpine;
window.LeaderLine = LeaderLine;
window.Html5Qrcode = Html5Qrcode;
window.md5 = MD5;

Alpine.start();

window.getRandomColor = () => {
  var letters = '0123456789ABCDEF';
  var color = '#';
  for (var i = 0; i < 6; i++) {
    color += letters[Math.floor(Math.random() * 16)];
  }
  return color;
}

window.generateColor = (str, alpha = 1) => {
  var rgb = [0, 0, 0];
  for (var i = 0; i < str.length; i++) {
    var v = str.charCodeAt(i);
    rgb[v % 3] = (rgb[i % 3] + (13 * (v % 13))) % 12;
  }
  var r = 4 + rgb[1];
  var g = 4 + rgb[2];
  var b = 4 + rgb[0];
  r = (r * 16) + r;
  g = (g * 16) + g;
  b = (b * 16) + b;
  var color = [r, g, b];
  return 'rgba(' + color[0] + ',' + color[1] + ',' + color[2] + ',' + alpha + ')';
};

function getInputSelection(el) {
  var start = 0, end = 0, normalizedValue, range,
    textInputRange, len, endRange;

  if (typeof el.selectionStart == "number" && typeof el.selectionEnd == "number") {
    start = el.selectionStart;
    end = el.selectionEnd;
  } else {
    range = document.selection.createRange();

    if (range && range.parentElement() == el) {
      len = el.value.length;
      normalizedValue = el.value.replace(/\r\n/g, "\n");

      // Create a working TextRange that lives only in the input
      textInputRange = el.createTextRange();
      textInputRange.moveToBookmark(range.getBookmark());

      // Check if the start and end of the selection are at the very end
      // of the input, since moveStart/moveEnd doesn't return what we want
      // in those cases
      endRange = el.createTextRange();
      endRange.collapse(false);

      if (textInputRange.compareEndPoints("StartToEnd", endRange) > -1) {
        start = end = len;
      } else {
        start = -textInputRange.moveStart("character", -len);
        start += normalizedValue.slice(0, start).split("\n").length - 1;

        if (textInputRange.compareEndPoints("EndToEnd", endRange) > -1) {
          end = len;
        } else {
          end = -textInputRange.moveEnd("character", -len);
          end += normalizedValue.slice(0, end).split("\n").length - 1;
        }
      }
    }
  }

  return {
    start: start,
    end: end
  };
}

function offsetToRangeCharacterMove(el, offset) {
  return offset - (el.value.slice(0, offset).split("\r\n").length - 1);
}

function setSelection(el, start, end) {
  if (typeof el.selectionStart == "number" && typeof el.selectionEnd == "number") {
    el.selectionStart = start;
    el.selectionEnd = end;
  } else if (typeof el.createTextRange != "undefined") {
    var range = el.createTextRange();
    var startCharMove = offsetToRangeCharacterMove(el, start);
    range.collapse(true);
    if (start == end) {
      range.move("character", startCharMove);
    } else {
      range.moveEnd("character", offsetToRangeCharacterMove(el, end));
      range.moveStart("character", startCharMove);
    }
    range.select();
  }
}
window.insertTextAtCaret = (el, text) => {
  var pos = getInputSelection(el).end;
  var newPos = pos + text.length;
  var val = el.value;
  el.value = val.slice(0, pos) + text + val.slice(pos);
  setSelection(el, newPos, newPos);
}

window.insertTag = (el, text, l = 0) => {
  var pos = getInputSelection(el).end;
  var newPos = pos + (text.length / 2) + l;
  var val = el.value;
  el.value = val.slice(0, pos) + text + val.slice(pos);
  setSelection(el, newPos, newPos);
}

window.timer = (expiry) => {
  let ival;
  return {
    expiry: expiry,
    remaining: null,
    init() {
      this.setRemaining()
      ival = setInterval(() => {
        this.setRemaining();
      }, 1000);
    },
    stop() {
      clearInterval(ival);
    },
    setRemaining() {
      const diff = this.expiry - new Date().getTime();
      this.remaining = parseInt(diff / 1000);
    },
    days() {
      return {
        value: this.remaining / 86400,
        remaining: this.remaining % 86400
      };
    },
    hours() {
      return {
        value: this.days().remaining / 3600,
        remaining: this.days().remaining % 3600
      };
    },
    minutes() {
      return {
        value: this.hours().remaining / 60,
        remaining: this.hours().remaining % 60
      };
    },
    seconds() {
      return {
        value: this.minutes().remaining,
      };
    },
    format(value) {
      return ("0" + parseInt(value)).slice(-2)
    },
    time() {
      return {
        days: this.format(this.days().value),
        hours: this.format(this.hours().value),
        minutes: this.format(this.minutes().value),
        seconds: this.format(this.seconds().value),
      }
    },
  }
}

window.lines = {};
window.linterval = {};

window.generateLine = (start, end, id = 'color') => {
  const line = new LeaderLine(start, end, {
    startPlug: 'disc',
    endPlug: 'disc',
    color: generateColor('coloravocado' + MD5(id).toString()),
    startSocket: 'right',
    endSocket: 'left'
  });

  if (linterval[id] != undefined) {
    clearInterval(linterval[id]);
    delete (linterval[id]);
  }

  linterval[id] = setInterval(() => {
    try {
      line.position();
    } catch { };
  }, 10)

  return line;
}

window.removeLine = async (line, id = 'color') => {
  if (line != undefined) {
    if (linterval[id] != undefined) {
      clearInterval(linterval[id]);
      delete (linterval[id]);
    }
    line.remove();
  }
  return null;
}