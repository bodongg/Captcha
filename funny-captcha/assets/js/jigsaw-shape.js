'use strict';

(function (root) {
  function edgeSigns(row, column, rows, columns) {
    const verticalSign = (r, c) => (r + c) % 2 === 0 ? 1 : -1;
    const horizontalSign = (r, c) => (r + c) % 2 === 0 ? -1 : 1;
    return {
      top: row === 0 ? 0 : -horizontalSign(row - 1, column),
      right: column === columns - 1 ? 0 : verticalSign(row, column),
      bottom: row === rows - 1 ? 0 : horizontalSign(row, column),
      left: column === 0 ? 0 : -verticalSign(row, column - 1)
    };
  }

  function edge(originX, originY, directionX, directionY, normalX, normalY, sign) {
    const point = (along, outward) =>
      `${originX + directionX * along + normalX * outward} ${originY + directionY * along + normalY * outward}`;
    if (sign === 0) return `L ${point(100, 0)}`;
    return [
      `L ${point(29, 0)}`,
      `C ${point(33, 0)} ${point(37, sign)} ${point(37, 7 * sign)}`,
      `C ${point(37, 11 * sign)} ${point(33, 14 * sign)} ${point(39, 19 * sign)}`,
      `C ${point(45, 25 * sign)} ${point(55, 25 * sign)} ${point(61, 19 * sign)}`,
      `C ${point(67, 14 * sign)} ${point(63, 11 * sign)} ${point(63, 7 * sign)}`,
      `C ${point(63, sign)} ${point(67, 0)} ${point(71, 0)}`,
      `L ${point(100, 0)}`
    ].join(' ');
  }

  function piecePath(row, column, rows, columns) {
    const sides = edgeSigns(row, column, rows, columns);
    return [
      'M 0 0',
      edge(0, 0, 1, 0, 0, -1, sides.top),
      edge(100, 0, 0, 1, 1, 0, sides.right),
      edge(100, 100, -1, 0, 0, 1, sides.bottom),
      edge(0, 100, 0, -1, -1, 0, sides.left),
      'Z'
    ].join(' ');
  }

  const api = { edgeSigns, piecePath };
  root.JigsawShape = api;
  if (typeof module !== 'undefined' && module.exports) module.exports = api;
})(typeof window === 'undefined' ? {} : window);
