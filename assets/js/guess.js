'use strict';

(() => {
  const pictures = JSON.parse(document.body.dataset.guessPictures || '[]');
  const backdrop = document.getElementById('guess-captcha');
  const title = document.getElementById('guess-title');
  const form = document.getElementById('guess-form');
  const frame = document.getElementById('guess-photo-frame');
  const photo = document.getElementById('guess-photo');
  const revealedPhoto = document.getElementById('guess-photo-reveal');
  const number = document.getElementById('guess-number');
  const answer = document.getElementById('guess-answer');
  const human = document.getElementById('guess-human');
  const status = document.getElementById('guess-status');
  const verify = document.getElementById('guess-verify');
  const reveal = document.getElementById('guess-reveal');
  const resultText = document.getElementById('guess-result');
  const next = document.getElementById('guess-next');
  let round = 0;

  function setStatus(message, isError = false) {
    status.textContent = message;
    status.classList.toggle('is-error', isError);
  }

  function drawRound() {
    frame.classList.add('is-resetting');
    frame.classList.remove('is-revealed');
    frame.classList.toggle('is-portrait', round >= 3);
    frame.classList.toggle('has-landscape-guess', round === 4);
    photo.src = pictures[round];
    photo.alt = `Mystery picture ${round + 1}`;
    revealedPhoto.removeAttribute('src');
    revealedPhoto.alt = '';
    requestAnimationFrame(() => requestAnimationFrame(() => frame.classList.remove('is-resetting')));
    number.textContent = `Picture ${round + 1} of ${pictures.length}`;
    answer.value = '';
    human.checked = false;
    form.hidden = false;
    reveal.hidden = true;
    verify.disabled = false;
    next.textContent = 'Next picture →';
    setStatus(round === 0 ? 'Human verified. Puzzle-solving skills: questionable. Now make your guess.' : 'Look closely, then make your guess.');
    answer.focus();
  }

  document.addEventListener('facepuzzle:puzzle-complete', () => {
    backdrop.hidden = false;
    drawRound();
    title.focus();
  });

  form.addEventListener('submit', async event => {
    event.preventDefault();
    const typed = answer.value.trim();
    if (!typed) {
      setStatus('Type your guess before verifying.', true);
      answer.focus();
      return;
    }
    if (!human.checked) {
      setStatus('Check “I’m not a robot” before verifying.', true);
      human.focus();
      return;
    }

    verify.disabled = true;
    setStatus('Consulting the highly judgmental CAPTCHA…');
    try {
      const response = await fetch('verify.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ stage: 'guess', token: document.body.dataset.token, round, answer: typed, humanChecked: human.checked })
      });
      const result = await response.json();
      if (!response.ok) throw new Error(result.error || 'Verification failed. Please try again.');
      if (!result.correct) {
        setStatus(result.message || 'Wrong guess. Try again.', true);
        answer.focus();
        answer.select();
        return;
      }

      revealedPhoto.src = result.reveal;
      revealedPhoto.alt = `Revealed picture: ${result.answer}`;
      if (revealedPhoto.decode) await revealedPhoto.decode().catch(() => {});
      photo.alt = '';
      form.hidden = true;
      reveal.hidden = false;
      resultText.textContent = 'Turning the picture around…';
      next.hidden = true;
      frame.classList.add('is-revealed');
      const finalMessage = result.completed
        ? `Correct — ${result.answer}! Five out of five. Human verified. Confidence level: unnecessarily high.`
        : `Correct — ${result.answer}! The hidden picture has been revealed.`;
      const delay = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 800;
      window.setTimeout(() => {
        resultText.textContent = finalMessage;
        next.hidden = false;
        if (result.completed) {
          next.textContent = 'Open your feed →';
          window.setTimeout(() => window.location.assign('home.php'), 2600);
        }
        next.focus();
      }, delay);
      round += 1;
    } catch (error) {
      setStatus(error.message, true);
    } finally {
      verify.disabled = false;
    }
  });

  next.addEventListener('click', () => {
    if (round >= pictures.length) {
      window.location.assign('home.php');
    } else {
      drawRound();
    }
  });
})();
