const test = require('node:test');
const assert = require('node:assert/strict');
const { edgeSigns, piecePath } = require('../assets/js/jigsaw-shape.js');

test('neighboring pieces have matching tabs and notches', () => {
  for (const [rows, columns] of [[2, 5], [5, 2]]) {
    for (let row = 0; row < rows; row += 1) {
      for (let column = 0; column < columns; column += 1) {
        const sides = edgeSigns(row, column, rows, columns);
        if (row === 0) assert.equal(sides.top, 0);
        if (column === 0) assert.equal(sides.left, 0);
        if (row === rows - 1) assert.equal(sides.bottom, 0);
        if (column === columns - 1) assert.equal(sides.right, 0);
        if (column < columns - 1) {
          assert.equal(sides.right, -edgeSigns(row, column + 1, rows, columns).left);
        }
        if (row < rows - 1) {
          assert.equal(sides.bottom, -edgeSigns(row + 1, column, rows, columns).top);
        }
      }
    }
  }
});

test('an interior piece has curved, closed jigsaw edges', () => {
  const path = piecePath(1, 1, 5, 2);
  assert.match(path, /^M 0 0 /);
  assert.match(path, / C /);
  assert.match(path, / Z$/);
});
