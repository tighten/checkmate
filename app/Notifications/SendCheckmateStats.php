<?php

namespace App\Notifications;

use App\Project;
use Illuminate\Notifications\Notification;
use NathanHeffley\LaravelSlackBlocks\Messages\SlackMessage;

class SendCheckmateStats extends Notification
{
    public function via($notifiable)
    {
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        $message = (new SlackMessage)
            // Provide a text-only fallback message
            ->content(sprintf('Here are your Laravel version stats for Checkmate projects! %s', config('app.url')))
            ->block(function ($block) {
                $block
                    ->type('section')
                    ->text([
                        'type' => 'mrkdwn',
                        'text' => 'Here are your Laravel version stats for Checkmate projects! ',
                    ])
                    ->accessory([
                        'type' => 'button',
                        'url' => config('app.url'),
                        'text' => [
                            'type' => 'plain_text',
                            'text' => 'Open Checkmate',
                            'emoji' => true,
                        ],
                    ]);
            });

        app(Project::class)->all()
            ->reject(function ($project) {
                return $project->status === Project::STATUS_CURRENT;
            })
            ->reject(function ($project) {
                return $project->ignored;
            })
            ->when(! config('app.show_private_repos'), function ($collection) {
                return $collection->reject(function ($project) {
                    return $project->is_private;
                });
            })
            ->sortByDesc('status')
            ->each(function ($project) use ($message) {
                $this->appendProjectToMessage($message, $project);
        });

        return $message;
    }

    private function appendProjectToMessage($message, Project $project)
    {
        return $message->attachment(function ($attachment) use ($project) {
            $scoreMoji = $project->status === Project::STATUS_INSECURE ? ':exclamation: ' : ':warning: ';
            $hexColor = $project->status === Project::STATUS_INSECURE ? '#cc0000' : '#F9C336';
            $attachment
                ->color($hexColor)
                ->block(function ($block) use ($project) {
                    $block
                        ->type('section')
                        ->text([
                            'type' => 'mrkdwn',
                            'text' => sprintf(
                                '*<%s|%s/%s>*',
                                $project->githubUrl,
                                $project->vendor,
                                $project->package
                            ),
                        ]);
                })
                ->block(function ($block) use ($project, $scoreMoji) {
                    $block
                        ->type('context')
                        ->elements([
                            [
                                'type' => 'mrkdwn',
                                'text' => sprintf(
                                    "%s *Current:* %s",
                                    $scoreMoji,
                                    $project->current_laravel_version,
                                ),
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => sprintf(
                                    "*Prescribed:* %s",
                                    $project->desiredLaravelVersion,
                                ),
                            ],
                        ]);
                })
                ->dividerBlock();
        });
    }
}
