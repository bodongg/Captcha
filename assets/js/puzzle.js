'use strict';

const pictures = JSON.parse(document.body.dataset.puzzles || '[]');
const token = document.body.dataset.token;
const board = document.getElementById('puzzle-board');
const tray = document.getElementById('piece-tray');
const statusLine = document.getElementById('puzzle-status');
const countLabel = document.getElementById('piece-count');
const photoLabel = document.getElementById('photo-number');
const verifyButton = document.getElementById('puzzle-verify');
const humanCheck = document.getElementById('puzzle-human');
const humanCheckWrap = document.getElementById('human-check-wrap');
const resetButton = document.getElementById('reset-puzzle');

let round = 0;
let selectedPiece = null;
let placements = Array(10).fill(null);

function setStatus(message, isError = false) {
  statusLine.textContent = message;
  statusLine.classList.toggle('is-error', isError);
}

function shuffledPieces() {
  const pieces = Array.from({ length: 10 }, (_, index) => index);
  for (let index = pieces.length - 1; index > 0; index -= 1) {
    const other = Math.floor(Math.random() * (index + 1));
    [pieces[index], pieces[other]] = [pieces[other], pieces[index]];
  }
  if (pieces.every((piece, index) => piece === index)) {
    [pieces[0], pieces[1]] = [pieces[1], pieces[0]];
  }
  return pieces;
}

function createPieceSvg(piece, picture, withPhoto) {
  const namespace = 'http://www.w3.org/2000/svg';
  const column = piece % picture.columns;
  const row = Math.floor(piece / picture.columns);
  const outline = JigsawShape.piecePath(row, column, picture.rows, picture.columns);
  const svg = document.createElementNS(namespace, 'svg');
  svg.setAttribute('viewBox', '0 0 100 100');
  svg.setAttribute('preserveAspectRatio', 'none');
  svg.setAttribute('overflow', 'visible');
  svg.setAttribute('class', 'jigsaw-svg');
  svg.setAttribute('aria-hidden', 'true');

  if (withPhoto) {
    const clipId = `piece-clip-${round}-${piece}`;
    const defs = document.createElementNS(namespace, 'defs');
    const clip = document.createElementNS(namespace, 'clipPath');
    clip.setAttribute('id', clipId);
    clip.setAttribute('clipPathUnits', 'userSpaceOnUse');
    const clipShape = document.createElementNS(namespace, 'path');
    clipShape.setAttribute('d', outline);
    clip.append(clipShape);
    defs.append(clip);
    svg.append(defs);

    const image = document.createElementNS(namespace, 'image');
    image.setAttribute('href', picture.image);
    image.setAttribute('x', String(-column * 100));
    image.setAttribute('y', String(-row * 100));
    image.setAttribute('width', String(picture.columns * 100));
    image.setAttribute('height', String(picture.rows * 100));
    image.setAttribute('preserveAspectRatio', 'none');
    image.setAttribute('clip-path', `url(#${clipId})`);
    svg.append(image);
  }

  const seam = document.createElementNS(namespace, 'path');
  seam.setAttribute('d', outline);
  seam.setAttribute('fill', withPhoto ? 'none' : '#e8f0f7');
  seam.setAttribute('stroke', withPhoto ? '#fff' : '#9db4ca');
  seam.setAttribute('stroke-width', withPhoto ? '1.6' : '1.3');
  seam.setAttribute('vector-effect', 'non-scaling-stroke');
  svg.append(seam);
  return svg;
}

function drawRound() {
  const picture = pictures[round];
  placements = Array(10).fill(null);
  selectedPiece = null;
  humanCheck.checked = false;
  humanCheckWrap.hidden = round !== pictures.length - 1;
  photoLabel.textContent = `Picture ${round + 1} of ${pictures.length}`;
  countLabel.textContent = '0 / 10 pieces';
  verifyButton.textContent = round === pictures.length - 1 ? 'Verify' : 'Next picture';
  verifyButton.disabled = false;
  board.replaceChildren();
  tray.replaceChildren();

  const boardWidth = picture.columns === 5 ? '390px' : '290px';
  const trayWidth = picture.columns === 5 ? '410px' : '310px';
  const tileAspect = picture.aspect * picture.rows / picture.columns;
  for (const area of [board, tray]) {
    area.style.gridTemplateColumns = `repeat(${picture.columns}, minmax(0, 1fr))`;
  }
  board.style.width = boardWidth;
  tray.style.width = trayWidth;

  for (let position = 0; position < 10; position += 1) {
    const slot = document.createElement('button');
    slot.type = 'button';
    slot.className = 'board-slot';
    slot.dataset.position = String(position);
    slot.style.aspectRatio = String(tileAspect);
    slot.setAttribute('aria-label', `Empty spot ${position + 1}`);
    slot.append(createPieceSvg(position, picture, false));
    slot.addEventListener('click', () => {
      if (selectedPiece === null) {
        setStatus('Select a scattered piece first.');
      } else {
        placePiece(selectedPiece, position);
      }
    });
    board.append(slot);
  }

  shuffledPieces().forEach(piece => {
    const tile = document.createElement('button');
    tile.type = 'button';
    tile.className = 'puzzle-piece';
    tile.dataset.piece = String(piece);
    tile.style.aspectRatio = String(tileAspect);
    tile.append(createPieceSvg(piece, picture, true));
    tile.style.setProperty('--tilt', `${(piece % 5 - 2) * 1.2}deg`);
    tile.setAttribute('aria-label', `Puzzle piece ${piece + 1}`);
    let ignoreClick = false;
    tile.addEventListener('pointerdown', event => {
      if (event.button !== 0) return;
      const startX = event.clientX;
      const startY = event.clientY;
      let ghost = null;
      let dragged = false;
      const bounds = tile.getBoundingClientRect();

      function moveGhost(moveEvent) {
        if (!dragged && Math.hypot(moveEvent.clientX - startX, moveEvent.clientY - startY) > 8) {
          dragged = true;
          ghost = tile.cloneNode(false);
          ghost.className = 'puzzle-drag-ghost';
          ghost.style.width = `${bounds.width}px`;
          ghost.style.height = `${bounds.height}px`;
          document.body.append(ghost);
          tile.classList.add('is-dragging');
          setStatus('Checking human coordination…');
        }
        if (dragged) {
          moveEvent.preventDefault();
          ghost.style.left = `${moveEvent.clientX - bounds.width / 2}px`;
          ghost.style.top = `${moveEvent.clientY - bounds.height / 2}px`;
        }
      }

      function finishDrag(upEvent) {
        window.removeEventListener('pointermove', moveGhost);
        window.removeEventListener('pointerup', finishDrag);
        if (!dragged) return;
        ignoreClick = true;
        setTimeout(() => { ignoreClick = false; }, 0);
        ghost.remove();
        tile.classList.remove('is-dragging');
        const target = document.elementFromPoint(upEvent.clientX, upEvent.clientY)?.closest('.board-slot');
        if (target) {
          placePiece(piece, Number(target.dataset.position));
        } else {
          setStatus('Drop the piece onto the picture board.', true);
        }
      }

      window.addEventListener('pointermove', moveGhost);
      window.addEventListener('pointerup', finishDrag);
    });
    tile.addEventListener('click', () => {
      if (ignoreClick) return;
      selectedPiece = piece;
      tray.querySelectorAll('.puzzle-piece').forEach(item => item.classList.toggle('is-selected', item === tile));
      setStatus('Checking human coordination… Now choose its spot.');
    });
    tray.append(tile);
  });

  setStatus('Click a piece, then click its matching space. Drag and drop also works.');
}

function placePiece(piece, position) {
  if (placements[position] !== null) return;
  const tile = tray.querySelector(`[data-piece="${piece}"]`);
  if (!tile) return;
  if (piece !== position) {
    setStatus('Suspiciously robot-like behavior. Try another spot.', true);
    return;
  }

  const slot = board.querySelector(`[data-position="${position}"]`);
  slot.replaceChildren(tile.firstElementChild);
  slot.classList.add('is-filled');
  slot.setAttribute('aria-label', `Correct piece in spot ${position + 1}`);
  tile.remove();
  placements[position] = piece;
  selectedPiece = null;
  countLabel.textContent = `${placements.filter(value => value !== null).length} / 10 pieces`;
  if (placements.every(value => value !== null)) {
    setStatus(round === pictures.length - 1 ? 'Checking human coordination… Surprisingly excellent.' : 'Picture complete. The investigation continues…');
  } else {
    setStatus('Piece accepted. Your human rating is rising.');
  }
}

async function verifyPuzzle() {
  if (placements.some(value => value === null)) {
    setStatus('Finish all ten pieces before continuing.', true);
    return;
  }
  if (round === pictures.length - 1 && !humanCheck.checked) {
    setStatus('Check “I’m not a robot” before verifying.', true);
    humanCheck.focus();
    return;
  }

  verifyButton.disabled = true;
  setStatus('Checking human coordination…');
  try {
    const response = await fetch('verify.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token, round, placements, humanChecked: humanCheck.checked })
    });
    const result = await response.json();
    if (!response.ok) throw new Error(result.error || 'Verification failed. Please try again.');
    if (result.next) {
      round += 1;
      drawRound();
      setStatus('Another picture? Yes. The CAPTCHA has trust issues.');
    } else if (result.completed) {
      document.querySelector('.captcha-backdrop').hidden = true;
      document.dispatchEvent(new Event('facepuzzle:puzzle-complete'));
    }
  } catch (error) {
    setStatus(error.message, true);
  } finally {
    verifyButton.disabled = false;
  }
}

verifyButton.addEventListener('click', verifyPuzzle);
resetButton.addEventListener('click', drawRound);
drawRound();
