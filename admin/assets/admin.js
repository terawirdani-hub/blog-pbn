(function () {
  document.querySelectorAll("[data-uploader]").forEach(function (root) {
    var input = root.querySelector("[data-live-file]");
    var preview = root.querySelector("[data-preview]");
    if (!input || !preview) {
      return;
    }
    input.addEventListener("change", function () {
      var file = input.files && input.files[0];
      if (!file) {
        return;
      }
      var url = URL.createObjectURL(file);
      preview.innerHTML = "";
      var img = document.createElement("img");
      img.alt = "";
      img.src = url;
      preview.appendChild(img);
    });
  });

  document.querySelectorAll("[data-count-target]").forEach(function (el) {
    var target = parseInt(el.getAttribute("data-count-target"), 10) || 0;
    var out = el.parentElement && el.parentElement.querySelector("[data-count-out]");
    if (!out) {
      return;
    }
    var tick = function () {
      var n = (el.value || "").length;
      out.textContent = n + " / " + target;
      out.classList.toggle("is-over", n > target);
      out.classList.toggle("is-ok", n > 0 && n <= target && n >= Math.floor(target * 0.7));
    };
    el.addEventListener("input", tick);
    tick();
  });

  var preset = document.getElementById("template-preset");
  function markOn(grid, value) {
    if (!grid) {
      return;
    }
    grid.querySelectorAll("label").forEach(function (lab) {
      var input = lab.querySelector("input");
      var on = input && input.value === value;
      lab.classList.toggle("is-on", !!on);
      if (input && on) {
        input.checked = true;
      }
    });
  }
  function syncPresetFromRadios() {
    if (!preset) {
      return;
    }
    var layout = document.querySelector("[data-layout-grid] input:checked");
    var palette = document.querySelector("[data-palette-grid] input:checked");
    if (!layout || !palette) {
      return;
    }
    Array.prototype.forEach.call(preset.options, function (opt) {
      if (opt.getAttribute("data-layout") === layout.value && opt.getAttribute("data-palette") === palette.value) {
        preset.value = opt.value;
      }
    });
  }
  if (preset) {
    preset.addEventListener("change", function () {
      var opt = preset.options[preset.selectedIndex];
      if (!opt) {
        return;
      }
      markOn(document.querySelector("[data-layout-grid]"), opt.getAttribute("data-layout"));
      markOn(document.querySelector("[data-palette-grid]"), opt.getAttribute("data-palette"));
    });
  }
  document.querySelectorAll("[data-layout-grid] input, [data-palette-grid] input").forEach(function (input) {
    input.addEventListener("change", function () {
      var grid = input.closest("[data-layout-grid], [data-palette-grid]");
      if (grid) {
        grid.querySelectorAll("label").forEach(function (lab) {
          lab.classList.toggle("is-on", lab.contains(input) && input.checked);
        });
      }
      syncPresetFromRadios();
    });
  });
})();
