<?php
abstract class ApiDocAnno extends Anno {

     public static function constTarget()
     {
         return AnnoElementType::TYPE_METHOD;
     }

     public static function constPolicy()
     {
         return AnnoPolicyEnum::POLICY_ACTIVE;
     }

     public static function constStruct()
     {
         return AnnoValueTypeEnum::TYPE_NORMAL;
     }

     public static function constAspect()
     {
         return null;
     }
 }