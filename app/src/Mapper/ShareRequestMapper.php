<?php
declare(strict_types=1);

namespace App\Mapper;

use App\Entity\ShareRequest;

class ShareRequestMapper
{
    public function mapShareRequestsToArray(array $shareRequests): array
    {
        $resolvedShareRequests = [];

        foreach ($shareRequests as $shareRequest) {
            $resolvedShareRequests[] = $this->mapShareRequestToArray($shareRequest);
        }

        return $resolvedShareRequests;
    }

    public function mapShareRequestToArray(ShareRequest $shareRequest): array
    {
        return [
            'id' => $shareRequest->getId(),
            'sender' => $shareRequest->getSender()->getPhoneNr(),
            'receiver' => $shareRequest->getReceiver()->getPhoneNr(),
            'name' => $shareRequest->getName(),
            'phone_nr' => $shareRequest->getPhoneNr(),
        ];
    }
}
