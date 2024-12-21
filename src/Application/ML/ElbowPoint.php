<?php

namespace App\Application\ML;

class ElbowPoint
{
    /**
     * @param float[] $sse
     */
    public static function find(array $sse): int
    {
        // Define the starting point (first value in the SSE array)
        $p1 = [0, $sse[0]];
        // Define the ending point (last value in the SSE array)
        $p2 = [count($sse) - 1, $sse[count($sse) - 1]];

        $maxAngle = 0; // To store the maximum angle
        $elbowPoint = 2; // To store the index of the elbow point

        // Loop through all points between the start and the end
        for ($i = 3; $i < count($sse) - 1; ++$i) {
            $p3 = [$i, $sse[$i]]; // Current point on the SSE curve

            // Calculate vectors: p1p2 (line from start to end) and p1p3 (line from start to current point)
            $v1 = [$p2[0] - $p1[0], $p2[1] - $p1[1]];
            $v2 = [$p3[0] - $p1[0], $p3[1] - $p1[1]];

            // Calculate the cosine of the angle between vectors v1 and v2
            $dotProduct = $v1[0] * $v2[0] + $v1[1] * $v2[1];
            $normV1 = sqrt($v1[0] ** 2 + $v1[1] ** 2); // Norm (magnitude) of vector v1
            $normV2 = sqrt($v2[0] ** 2 + $v2[1] ** 2); // Norm (magnitude) of vector v2

            $cosTheta = $dotProduct / ($normV1 * $normV2); // Compute cosine of the angle
            $angle = acos(max(-1.0, min(1.0, $cosTheta))) * (180 / pi()); // Convert radians to degrees

            // Update the maximum angle and elbow point if a larger angle is found
            if ($angle > $maxAngle) {
                $maxAngle = $angle;
                $elbowPoint = $i;
            }
        }

        // Return the elbow point (add 1 since k starts at 1 while array indices start at 0)
        return $elbowPoint + 1;
    }
}
