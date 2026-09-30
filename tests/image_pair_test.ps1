$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing
$images = Join-Path $PSScriptRoot '..\assets\images'

function Get-RedMarkerSamples($path) {
    $bitmap = [System.Drawing.Bitmap]::new((Resolve-Path -LiteralPath $path).Path)
    try {
        $red = 0
        for ($y = 0; $y -lt $bitmap.Height; $y += 8) {
            for ($x = 0; $x -lt $bitmap.Width; $x += 8) {
                $pixel = $bitmap.GetPixel($x, $y)
                if ($pixel.R -gt 180 -and $pixel.G -lt 110 -and $pixel.B -lt 110) { $red++ }
            }
        }
        return $red
    } finally {
        $bitmap.Dispose()
    }
}

foreach ($name in @('dacera', 'james')) {
    $masked = Get-RedMarkerSamples (Join-Path $images "$name-guess.jpg")
    $revealed = Get-RedMarkerSamples (Join-Path $images "$name.jpg")
    if ($masked -le ($revealed * 2)) {
        throw "$name image pair is reversed: guess=$masked red samples, reveal=$revealed red samples."
    }
}
Write-Output 'Dacera and James masked/reveal images are in the correct order.'
