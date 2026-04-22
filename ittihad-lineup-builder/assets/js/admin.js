/**
 * Ittihad Lineup Builder — Admin JS
 * Drag & Drop Lineup Builder
 * Developer: محمد بلعيد | github: x414i
 */

(function ($) {
  'use strict';

  /* ── State ──────────────────────────────────────────────────────────────── */
  const state = {
    lineupId:    parseInt(window.ILB && ILB.builder && ILB.builder.lineupId) || 0,
    teamId:      0,
    sportType:   'football',
    fieldType:   'default',
    fieldImgUrl: '',
    players:     [],   // loaded from server
    placed:      {},   // { playerId: { x, y, name, number, position, photo } }
    dragging:    null, // { playerId, startX, startY, offsetX, offsetY, el }
  };

  /* ── Cache ───────────────────────────────────────────────────────────────── */
  let $builder, $pitch, $playersList, $activePlayers, $playerCount, $dropHint,
      $statusMsg, $saveBtn, $teamSelect, $sportSelect, $lineupSelect,
      $lineupNameInput, $fieldTypeSelect, $fieldUploadWrap, $fieldImgUrl, $playerSearch;

  function initDOM() {
    $builder         = $('#ilb-builder');
    $pitch           = $('#ilb-pitch');
    $playersList     = $('#ilb-players-list');
    $activePlayers   = $('#ilb-active-players');
    $playerCount     = $('#ilb-player-count');
    $dropHint        = $('#ilb-drop-hint');
    $statusMsg       = $('#ilb-status-msg');
    $saveBtn         = $('#ilb-save-btn');
    $teamSelect      = $('#ilb-team-select');
    $sportSelect     = $('#ilb-sport-select');
    $lineupSelect    = $('#ilb-lineup-select');
    $lineupNameInput = $('#ilb-lineup-name');
    $fieldTypeSelect = $('#ilb-field-type-select');
    $fieldUploadWrap = $('#ilb-custom-field-upload');
    $fieldImgUrl     = $('#ilb-field-img-url');
    $playerSearch    = $('#ilb-player-search');
  }

  /* ── Init ────────────────────────────────────────────────────────────────── */
  function init() {
    initDOM();
    if (!$builder.length) return; // Not on builder page

    // Read initial state from DOM
    state.lineupId   = parseInt($builder.data('lineup-id'))  || 0;
    state.sportType  = $builder.data('sport-type')  || 'football';
    state.fieldType  = $builder.data('field-type')  || 'default';
    state.fieldImgUrl= $builder.data('field-img')   || '';
    state.teamId     = parseInt($('#ilb-team-select').val()) || 0;

    // Enable save button if lineup exists
    if (state.lineupId) {
      $saveBtn.prop('disabled', false);
    }

    // Load lineup data if we have one
    if (state.lineupId) {
      loadLineupData(state.lineupId);
    }

    // Load players for selected team
    if (state.teamId) {
      loadTeamPlayers(state.teamId);
    }

    bindEvents();
    initDragDrop();
    updatePitchSport();
  }

  /* ── Event Bindings ─────────────────────────────────────────────────────── */
  function bindEvents() {
    // Lineup selector
    $('#ilb-lineup-select').on('change', function () {
      const id = parseInt($(this).val());
      if (id) {
        window.location.href = `${window.location.pathname}?page=ilb_builder&lineup_id=${id}`;
      }
    });

    // Team selector
    $('#ilb-team-select').on('change', function () {
      state.teamId = parseInt($(this).val()) || 0;
      if (state.teamId) {
        const sport = $(this).find(':selected').data('sport') || 'football';
        $('#ilb-sport-select').val(sport);
        state.sportType = sport;
        updatePitchSport();
        loadTeamPlayers(state.teamId);
      } else {
        $playersList.html('<p class="ilb-empty-msg">' + ILB.strings.selectTeam + '</p>');
      }
    });

    // Sport selector
    $('#ilb-sport-select').on('change', function () {
      state.sportType = $(this).val();
      updatePitchSport();
    });

    // Field type selector
    $('#ilb-field-type-select').on('change', function () {
      state.fieldType = $(this).val();
      if (state.fieldType === 'custom') {
        $fieldUploadWrap.show();
      } else {
        $fieldUploadWrap.hide();
        $pitch.css('background-image', '');
      }
    });

    // Field image upload
    $('#ilb-upload-field').on('click', function () {
      openMediaUploader(function (url) {
        state.fieldImgUrl = url;
        $fieldImgUrl.val(url);
        $pitch.css({ 'background-image': `url(${url})`, 'background-size': 'cover', 'background-position': 'center' });
      });
    });

    // Save button
    $saveBtn.on('click', saveLineup);

    // New lineup modal
    $('#ilb-new-lineup-btn').on('click', function () {
      $('#ilb-new-lineup-modal').show();
      $('#ilb-new-lineup-name').focus();
    });

    $('#ilb-cancel-modal-btn, .ilb-modal__backdrop').on('click', function () {
      $('#ilb-new-lineup-modal').hide();
    });

    $('#ilb-create-lineup-btn').on('click', createNewLineup);

    // Clear all players
    $('#ilb-clear-btn').on('click', function () {
      if (!confirm(ILB.strings.confirmRemove || 'هل تريد إزالة جميع اللاعبين؟')) return;
      clearPitch();
    });

    // Panel toggles
    $(document).on('click', '.ilb-panel__head', function (e) {
      if ($(e.target).hasClass('ilb-panel__toggle')) return;
      const $body = $(this).next('.ilb-panel__body');
      $body.slideToggle(180);
    });

    $(document).on('click', '.ilb-panel__toggle', function () {
      const targetId = $(this).data('target');
      const $body = $(`#${targetId}`);
      $body.slideToggle(180);
      $(this).text($body.is(':visible') ? '▼' : '▲');
    });

    // Copy shortcode buttons
    $(document).on('click', '.ilb-copy-btn', function () {
      const text = $(this).data('clipboard').replace(/&quot;/g, '"');
      copyToClipboard(text);
      const $btn = $(this);
      $btn.text('✓ تم النسخ');
      setTimeout(() => $btn.text('📋 نسخ'), 2000);
    });

    // Media uploader for meta box
    $(document).on('click', '#ilb_upload_field_btn', function (e) {
      e.preventDefault();
      openMediaUploader(function (url) {
        $('#ilb_field_image').val(url);
        $('#ilb_field_preview').attr('src', url).show();
        $('#ilb_remove_field_btn').show();
      });
    });

    $(document).on('click', '#ilb_remove_field_btn', function (e) {
      e.preventDefault();
      $('#ilb_field_image').val('');
      $('#ilb_field_preview').hide().attr('src', '');
      $(this).hide();
    });

    // Field type select in meta box
    $('#ilb_field_type').on('change', function () {
      if ($(this).val() === 'custom') {
        $('#ilb_custom_field_wrap').show();
      } else {
        $('#ilb_custom_field_wrap').hide();
      }
    });

    // Team select in lineup meta box syncs sport
    $('#ilb_lineup_team').on('change', function () {
      const sport = $(this).find(':selected').data('sport') || 'football';
      if (sport) $('#ilb_lineup_sport').val(sport);
    });

    // Player search
    $playerSearch.on('input', function () {
      const q = $(this).val().trim().toLowerCase();
      $playersList.find('.ilb-player-item').each(function () {
        const name = $(this).data('name').toLowerCase();
        $(this).toggle(!q || name.includes(q));
      });
    });
  }

  /* ── Drag & Drop (Vanilla) ───────────────────────────────────────────────── */
  function initDragDrop() {
    const pitch = document.getElementById('ilb-pitch');
    if (!pitch) return;

    let dragData = null; // { playerId, offsetX, offsetY, el, isCopy }

    /* ── Touch & Mouse: drag from sidebar ── */
    $(document).on('mousedown touchstart', '.ilb-player-item:not(.ilb-on-field)', function (e) {
      if ($(this).hasClass('ilb-on-field')) return;

      e.preventDefault();
      const playerId = parseInt($(this).data('id'));
      const player   = state.players.find(p => p.id === playerId);
      if (!player) return;

      const ev      = e.originalEvent.touches ? e.originalEvent.touches[0] : e.originalEvent;
      const rect    = this.getBoundingClientRect();
      const offsetX = ev.clientX - rect.left - rect.width / 2;
      const offsetY = ev.clientY - rect.top  - rect.height / 2;

      // Create ghost element
      const $ghost = createPlayerEl(player, 0, 0, true);
      $ghost.addClass('ilb-ghost').css({
        position: 'fixed',
        left:     ev.clientX - 22,
        top:      ev.clientY - 22,
        zIndex:   9999,
        opacity:  0.75,
        pointerEvents: 'none',
        transition: 'none',
      });
      $('body').append($ghost);

      dragData = { playerId, player, offsetX, offsetY, $ghost, isCopy: true };
    });

    /* ── Touch & Mouse: drag existing player on pitch ── */
    $(document).on('mousedown touchstart', '.ilb-pitch-player', function (e) {
      e.preventDefault();
      e.stopPropagation();
      const playerId = parseInt($(this).data('id'));
      const player   = state.placed[playerId];
      if (!player) return;

      const ev      = e.originalEvent.touches ? e.originalEvent.touches[0] : e.originalEvent;
      const $el     = $(this);
      const rect    = this.getBoundingClientRect();
      const offsetX = ev.clientX - (rect.left + rect.width / 2);
      const offsetY = ev.clientY - (rect.top  + rect.height / 2);

      $el.addClass('ilb-dragging');
      dragData = { playerId, player, offsetX, offsetY, $ghost: $el, isExisting: true };
    });

    /* ── Move ── */
    $(document).on('mousemove touchmove', function (e) {
      if (!dragData) return;
      e.preventDefault();
      const ev = e.originalEvent.touches ? e.originalEvent.touches[0] : e.originalEvent;

      if (dragData.isCopy) {
        dragData.$ghost.css({ left: ev.clientX - 22, top: ev.clientY - 22 });
      } else if (dragData.isExisting) {
        const pitchRect = pitch.getBoundingClientRect();
        const x = ((ev.clientX - pitchRect.left) / pitchRect.width)  * 100;
        const y = ((ev.clientY - pitchRect.top)  / pitchRect.height) * 100;
        const cx = Math.max(3, Math.min(97, x));
        const cy = Math.max(3, Math.min(97, y));
        dragData.$ghost.css({ left: `${cx}%`, top: `${cy}%` });
      }

      // Highlight pitch on hover
      const pitchRect = pitch.getBoundingClientRect();
      const over = ev.clientX >= pitchRect.left && ev.clientX <= pitchRect.right
                && ev.clientY >= pitchRect.top  && ev.clientY <= pitchRect.bottom;
      $pitch.toggleClass('ilb-drag-over', over);
    });

    /* ── Drop / End ── */
    $(document).on('mouseup touchend', function (e) {
      if (!dragData) return;
      const ev = e.originalEvent.changedTouches ? e.originalEvent.changedTouches[0] : e.originalEvent;

      const pitchRect = pitch.getBoundingClientRect();
      const over = ev.clientX >= pitchRect.left && ev.clientX <= pitchRect.right
                && ev.clientY >= pitchRect.top  && ev.clientY <= pitchRect.bottom;

      $pitch.removeClass('ilb-drag-over');

      if (over) {
        const x = ((ev.clientX - pitchRect.left) / pitchRect.width)  * 100;
        const y = ((ev.clientY - pitchRect.top)  / pitchRect.height) * 100;
        const cx = Math.max(3, Math.min(97, x));
        const cy = Math.max(3, Math.min(97, y));

        placePlayer(dragData.player, cx, cy);

        if (dragData.isCopy) {
          // mark sidebar item as on-field
          $(`.ilb-player-item[data-id="${dragData.playerId}"]`).addClass('ilb-on-field');
        }
      }

      // Cleanup
      if (dragData.isCopy && dragData.$ghost) dragData.$ghost.remove();
      if (dragData.isExisting) dragData.$ghost.removeClass('ilb-dragging');

      dragData = null;
    });

    /* ── Remove player button on pitch ── */
    $(document).on('click', '.ilb-pitch-player__remove-btn', function (e) {
      e.stopPropagation();
      const $player = $(this).closest('.ilb-pitch-player');
      const playerId = parseInt($player.data('id'));
      removePlayerFromPitch(playerId);
    });
  }

  /* ── Place / Remove Players ─────────────────────────────────────────────── */
  function placePlayer(player, x, y) {
    // Remove if already on pitch (repositioning)
    if (state.placed[player.id]) {
      $(`.ilb-pitch-player[data-id="${player.id}"]`).remove();
    }

    state.placed[player.id] = { ...player, x, y };
    updateDropHint();

    const $el = createPlayerEl(player, x, y, false);
    $pitch.append($el);
    updateActivePlayers();
    updatePlayerCount();
  }

  function removePlayerFromPitch(playerId) {
    if (!state.placed[playerId]) return;
    delete state.placed[playerId];
    $(`.ilb-pitch-player[data-id="${playerId}"]`).remove();
    $(`.ilb-player-item[data-id="${playerId}"]`).removeClass('ilb-on-field');
    updateActivePlayers();
    updatePlayerCount();
    updateDropHint();
  }

  function clearPitch() {
    state.placed = {};
    $pitch.find('.ilb-pitch-player').remove();
    $playersList.find('.ilb-player-item').removeClass('ilb-on-field');
    updateActivePlayers();
    updatePlayerCount();
    updateDropHint();
  }

  /* ── Create Player DOM Element ──────────────────────────────────────────── */
  function createPlayerEl(player, x, y, isGhost) {
    const photoHtml = player.photo
      ? `<img src="${escHtml(player.photo)}" alt="${escHtml(player.name)}" loading="lazy" />`
      : `<div class="ilb-pitch-player__avatar-ph">${escHtml(player.name.charAt(0))}</div>`;

    const numHtml = player.number
      ? `<span class="ilb-pitch-player__num">${escHtml(player.number)}</span>`
      : '';

    const $el = $('<div>')
      .addClass(isGhost ? 'ilb-pitch-player ilb-ghost-player' : 'ilb-pitch-player')
      .attr('data-id', player.id);

    if (!isGhost) {
      $el.css({ left: `${x}%`, top: `${y}%` });
    }

    $el.html(`
      <div class="ilb-pitch-player__avatar">
        ${photoHtml}
        ${numHtml}
        ${!isGhost ? '<button type="button" class="ilb-pitch-player__remove-btn" title="إزالة">✕</button>' : ''}
      </div>
      <div class="ilb-pitch-player__label">${escHtml(player.name)}</div>
    `);

    return $el;
  }

  /* ── Update Active Players Sidebar ─────────────────────────────────────── */
  function updateActivePlayers() {
    $activePlayers.empty();
    const placed = Object.values(state.placed);

    placed.forEach(p => {
      const $row = $(`
        <div class="ilb-active-player">
          <span class="ilb-active-player__name">${escHtml(p.name)}</span>
          <button type="button" class="ilb-active-player__remove" data-id="${p.id}" title="إزالة">✕</button>
        </div>
      `);
      $activePlayers.append($row);
    });

    if (!placed.length) {
      $activePlayers.html('<p class="ilb-empty-msg" style="padding:8px 0;font-size:12px;">لم يتم إضافة لاعبين بعد</p>');
    }
  }

  $(document).on('click', '.ilb-active-player__remove', function () {
    removePlayerFromPitch(parseInt($(this).data('id')));
  });

  function updatePlayerCount() {
    $playerCount.text(Object.keys(state.placed).length);
  }

  function updateDropHint() {
    const hasPlayers = Object.keys(state.placed).length > 0;
    $dropHint.toggleClass('ilb-hidden', hasPlayers);
  }

  function updatePitchSport() {
    $pitch.attr('data-sport', state.sportType);
  }

  /* ── Load Team Players ──────────────────────────────────────────────────── */
  function loadTeamPlayers(teamId) {
    $playersList.html('<p class="ilb-empty-msg">جارٍ التحميل...</p>');

    $.ajax({
      url:  ILB.ajaxUrl,
      type: 'POST',
      data: {
        action:  'ilb_get_team_players',
        nonce:   ILB.nonce,
        team_id: teamId,
      },
      success(res) {
        if (res.success) {
          state.players = res.data;
          renderPlayersList(res.data);
        }
      },
      error() {
        $playersList.html('<p class="ilb-empty-msg">حدث خطأ في التحميل</p>');
      },
    });
  }

  function renderPlayersList(players) {
    $playersList.empty();

    if (!players.length) {
      $playersList.html(`<p class="ilb-empty-msg">${ILB.strings.noPlayers}</p>`);
      return;
    }

    players.forEach(p => {
      const onField = !!state.placed[p.id];
      const photoHtml = p.photo
        ? `<img src="${escHtml(p.photo)}" class="ilb-player-item__thumb" alt="${escHtml(p.name)}" loading="lazy" />`
        : `<div class="ilb-player-item__thumb-placeholder">${escHtml(p.name.charAt(0))}</div>`;

      const numHtml = p.number
        ? `<span class="ilb-player-item__num">${escHtml(p.number)}</span>`
        : '';

      const $item = $(`
        <div class="ilb-player-item${onField ? ' ilb-on-field' : ''}" data-id="${p.id}" data-name="${escHtml(p.name)}">
          ${photoHtml}
          <div class="ilb-player-item__info">
            <span class="ilb-player-item__name">${escHtml(p.name)}</span>
            <span class="ilb-player-item__pos">${escHtml(p.position || '')}</span>
          </div>
          ${numHtml}
        </div>
      `);

      $playersList.append($item);
    });
  }

  /* ── Load Lineup Data ───────────────────────────────────────────────────── */
  function loadLineupData(lineupId) {
    $.ajax({
      url:  ILB.ajaxUrl,
      type: 'POST',
      data: {
        action:    'ilb_get_lineup_data',
        nonce:     ILB.nonce,
        lineup_id: lineupId,
      },
      success(res) {
        if (!res.success) return;
        const d = res.data;

        state.teamId    = parseInt(d.team_id)   || 0;
        state.sportType = d.sport_type || 'football';
        state.fieldType = d.field_type || 'default';
        state.fieldImgUrl = d.field_img || '';

        // Sync selects
        if (state.teamId) {
          $('#ilb-team-select').val(state.teamId);
          loadTeamPlayers(state.teamId);
        }
        $('#ilb-sport-select').val(state.sportType);
        updatePitchSport();

        if (state.fieldType === 'custom' && state.fieldImgUrl) {
          $pitch.css({ 'background-image': `url(${state.fieldImgUrl})`, 'background-size': 'cover', 'background-position': 'center' });
        }

        // Place players
        if (d.positions && typeof d.positions === 'object') {
          Object.entries(d.positions).forEach(([id, pos]) => {
            state.placed[parseInt(id)] = {
              id:       parseInt(id),
              name:     pos.name     || '',
              number:   pos.number   || '',
              position: pos.position || '',
              photo:    pos.photo    || '',
              x:        pos.x,
              y:        pos.y,
            };
            const $el = createPlayerEl(state.placed[parseInt(id)], pos.x, pos.y, false);
            $pitch.append($el);
          });
          updateDropHint();
          updatePlayerCount();
        }
      },
    });
  }

  /* ── Save Lineup ────────────────────────────────────────────────────────── */
  function saveLineup() {
    if (!state.lineupId) {
      showToast('الرجاء إنشاء تشكيلة أولاً', 'error');
      return;
    }

    const positions = {};
    Object.entries(state.placed).forEach(([id, p]) => {
      positions[id] = {
        x:        p.x,
        y:        p.y,
        name:     p.name,
        number:   p.number,
        position: p.position,
        photo:    p.photo,
      };
    });

    $saveBtn.text(ILB.strings.saving).prop('disabled', true);
    setStatus(ILB.strings.saving);

    $.ajax({
      url:  ILB.ajaxUrl,
      type: 'POST',
      data: {
        action:      'ilb_save_lineup',
        nonce:       ILB.nonce,
        lineup_id:   state.lineupId,
        positions:   JSON.stringify(positions),
        team_id:     state.teamId,
        sport_type:  state.sportType,
        lineup_name: $lineupNameInput.val() || '',
      },
      success(res) {
        if (res.success) {
          showToast(ILB.strings.saved);
          setStatus(ILB.strings.saved);
          $saveBtn.text('💾 حفظ التشكيلة').prop('disabled', false);
        } else {
          showToast(res.data.message || ILB.strings.error, 'error');
          setStatus('');
          $saveBtn.text('💾 حفظ التشكيلة').prop('disabled', false);
        }
      },
      error() {
        showToast(ILB.strings.error, 'error');
        $saveBtn.text('💾 حفظ التشكيلة').prop('disabled', false);
        setStatus('');
      },
    });
  }

  /* ── Create New Lineup ──────────────────────────────────────────────────── */
  function createNewLineup() {
    const name = $('#ilb-new-lineup-name').val().trim();
    if (!name) {
      $('#ilb-new-lineup-name').focus();
      return;
    }

    $('#ilb-create-lineup-btn').text('جارٍ الإنشاء...').prop('disabled', true);

    // Create post via AJAX (wp-ajax + wp_insert_post)
    $.ajax({
      url:  ILB.ajaxUrl,
      type: 'POST',
      data: {
        action: 'ilb_create_lineup',
        nonce:  ILB.nonce,
        name:   name,
      },
      success(res) {
        if (res.success && res.data.id) {
          window.location.href = `${window.location.pathname}?page=ilb_builder&lineup_id=${res.data.id}`;
        } else {
          showToast('فشل الإنشاء، يرجى المحاولة مجدداً', 'error');
          $('#ilb-create-lineup-btn').text('إنشاء').prop('disabled', false);
        }
      },
      error() {
        showToast(ILB.strings.error, 'error');
        $('#ilb-create-lineup-btn').text('إنشاء').prop('disabled', false);
      },
    });
  }

  /* ── Media Uploader ─────────────────────────────────────────────────────── */
  let mediaFrame;
  function openMediaUploader(callback) {
    if (mediaFrame) {
      mediaFrame.open();
      return;
    }

    mediaFrame = wp.media({
      title:    'اختر صورة الملعب',
      button:   { text: 'اختيار' },
      multiple: false,
      library:  { type: 'image' },
    });

    mediaFrame.on('select', function () {
      const attachment = mediaFrame.state().get('selection').first().toJSON();
      callback(attachment.url);
    });

    mediaFrame.open();
  }

  /* ── Helpers ────────────────────────────────────────────────────────────── */
  function showToast(msg, type = 'success') {
    let $toast = $('#ilb-toast');
    if (!$toast.length) {
      $toast = $('<div id="ilb-toast" class="ilb-toast"></div>').appendTo('body');
    }
    $toast
      .text(msg)
      .removeClass('ilb-toast--success ilb-toast--error')
      .addClass(type === 'error' ? 'ilb-toast--error' : '')
      .addClass('ilb-toast--show');

    setTimeout(() => $toast.removeClass('ilb-toast--show'), 3000);
  }

  function setStatus(msg) {
    $statusMsg.text(msg);
  }

  function copyToClipboard(text) {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(text);
    } else {
      const ta = document.createElement('textarea');
      ta.value = text;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
    }
  }

  function escHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  /* ── AJAX: create new lineup (hook into wp_ajax) ── */
  // This uses a separate action registered in the main plugin class
  // Add the registration in the main PHP file
  $(document).on('ajaxSend', function () {});

  /* ── Auto lazy-load images ──────────────────────────────────────────────── */
  function lazyLoadImages() {
    if ('IntersectionObserver' in window) {
      const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
          if (e.isIntersecting) {
            const img = e.target;
            if (img.dataset.src) {
              img.src = img.dataset.src;
            }
            img.classList.add('ilb-loaded');
            obs.unobserve(img);
          }
        });
      });
      document.querySelectorAll('img[loading="lazy"]').forEach(img => obs.observe(img));
    }
  }

  /* ── Boot ────────────────────────────────────────────────────────────────── */
  $(document).ready(function () {
    init();
    lazyLoadImages();

    // Handle regular img loading for thumbnail fade-in
    $(document).on('load', 'img', function () {
      $(this).addClass('ilb-loaded');
    }).on('error', 'img', function () {
      $(this).closest('.ilb-player-item__thumb').replaceWith(
        `<div class="ilb-player-item__thumb-placeholder">${$(this).attr('alt')?.charAt(0) || '?'}</div>`
      );
    });
  });

}(jQuery));
