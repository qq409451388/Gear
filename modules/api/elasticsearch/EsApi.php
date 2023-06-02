<?php

class EsApi implements EzHelper
{
    public static $esServerUrl;

    public static function setUrl($url) {
        self::$esServerUrl = $url;
    }

    /**
     * @param $index string 索引名
     * @param $quertString string 查询匹配内容
     * @param $s int timestamp
     * @param $e int timestamp
     * @param $header array<string> cookie header
     * @return array<EsResponse>
     * @throws Exception
     */
    public static function multiSearchLite($index, $quertString, $s, $e, $header = []) {
        DBC::assertNonNull(self::$esServerUrl, "[EsApi] Must Call Function setUrl first!", 0, GearUnsupportedOperationException::class);
        $esSearchRequest = new EsSearchRequest();
        $esSearchRequest->indexInfo = new EsIndexInfo();
        $esSearchRequest->indexInfo->index = [$index];
        $esSearchRequest->queryBody = new EsSearchQueryBody();
        $sort = new EsSearchSort();
        $esSearchRequest->queryBody->sort = [
            ["@timestamp" => $sort]
        ];
        $esSearchRequest->queryBody->query = new EsSearchQuery();
        $esSearchRequest->queryBody->query->bool = new EsSearchQueryBool();
        $esSearchRequest->queryBody->query->bool
            ->addMust(EsSearchQueryBoolItem::createQuery($quertString))
            ->addMust(EsSearchQueryBoolItem::createRange("@timestamp", $s*1000, $e*1000));
        return self::multiSearch($esSearchRequest, $header);
    }

    /**
     * @param EsSearchRequest $request
     * @param $header
     * @return array<EsResponse>
     * @throws Exception
     */
    public static function multiSearch(EsSearchRequest $request, $header = []) {
        DBC::assertNonNull(self::$esServerUrl, "[EsApi] Must Call Function setUrl first!", 0, GearUnsupportedOperationException::class);
        $curl = new EzCurl();
        $curl->setUrl(self::$esServerUrl."/_msearch");
        $curl->setHeader($header);
        $res = $curl->post($request->toNdJson(), EzCurl::POSTTYPE_NDJSON);
        $resObj = EzCollectionUtils::decodeJson($res);
        if (empty($resObj['responses'])) {
            return EzCollectionUtils::emptyList();
        }
        $result = [];
        foreach ($resObj['responses'] as $respons) {
            $result[] = EsResponse::create($respons);
        }
        return $result;
    }
}
