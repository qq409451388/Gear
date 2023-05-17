<?php

class EzDrawerColorEnum
{
    const WHITE = "WHITE";
    const WHITE_RGB = [255, 255, 255];
    const BLACK = "BLACK";
    const BLACK_RGB = [0, 0, 0];

    const RGB_COLORS = [
        self::WHITE => self::WHITE_RGB,
        self::BLACK => self::BLACK_RGB
    ];
}
