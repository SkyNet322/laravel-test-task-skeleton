<?php

namespace Tests\Http\Controller;

use Generator;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;

class EmployeeScheduleControllerTest extends TestCase
{
    private const GET_EMPLOYEE_SCHEDULE_ENDPOINT_URL = '/employee-schedule';

    /**
     * @dataProvider getEmployeesData
     */
    public function testScheduleMustBeInValidFormat(int $employeeId): void
    {
        $workDay = '2021-01-11';

        $testResponse = $this->requestSchedule($employeeId, $workDay, $workDay);

        $testResponse->assertSuccessful();

        $this->assertResponseContainsSchedule($testResponse);
    }

    /**
     * @dataProvider getEmployeesData
     *
     * @param mixed[] $employeeWorkDaySchedule
     */
    public function testWorkDayScheduleMustContainsEmployeeSchedule(int $employeeId, array $employeeWorkDaySchedule): void
    {
        $workDay = '2021-01-11';

        $testResponse = $this->requestSchedule($employeeId, $workDay, $workDay);

        $schedule = $testResponse->json('schedule') ?? [];
        $workDaySchedule = $this->findDaySchedule($workDay, $schedule);

        $this->assertNotEmpty($workDaySchedule);
        $this->assertEquals($employeeWorkDaySchedule, $workDaySchedule['timeRanges']);
    }

    /**
     * @dataProvider getEmployeesData
     */
    public function testWeekendMustBeExcludedFromWorkSchedule(int $employeeId): void
    {
        $weekendStart = '2021-01-16';
        $weekendEnd = '2021-01-17';

        $testResponse = $this->requestSchedule($employeeId, $weekendStart, $weekendEnd);

        $schedule = $testResponse->json('schedule');

        $this->assertNotNull($schedule);
        $this->assertNull($this->findDaySchedule($weekendStart, $schedule));
        $this->assertNull($this->findDaySchedule($weekendEnd, $schedule));
    }

    /**
     * @dataProvider getEmployeesData
     */
    public function testHolidaysMustBeExcludedFromSchedule(int $employeeId): void
    {
        $holiday = '2021-02-23';
        $workDay = '2021-02-24';

        $testResponse = $this->requestSchedule($employeeId, $holiday, $workDay);

        $schedule = $testResponse->json('schedule');

        $this->assertNotNull($schedule);
        $this->assertNull($this->findDaySchedule($holiday, $schedule));
        $this->assertNotNull($this->findDaySchedule($workDay, $schedule));
    }

    /**
     * @dataProvider getEmployeesData
     */
    public function testInvalidRequestMustReturnErrors(int $employeeId): void
    {
        $invalidDate = 'invalid date';

        $testResponse = $this->requestSchedule($employeeId, $invalidDate, $invalidDate);

        $testResponse->assertStatus(JsonResponse::HTTP_BAD_REQUEST);

        $this->assertResponseContainsErrors($testResponse);
    }

    public function getEmployeesData(): Generator
    {
        yield 'Employee who works from the late morning' =>  [
            'id' => 1,
            'workDaySchedule' => [
                [
                    'start' => '10:00',
                    'end' => '13:00',
                ],
                [
                    'start' => '14:00',
                    'end' => '19:00',
                ],
            ],
        ];

        yield 'Employee who works from the early morning' => [
            'id' => 2,
            'workDaySchedule' => [
                [
                    'start' => '09:00',
                    'end' => '12:00',
                ],
                [
                    'start' => '13:00',
                    'end' => '18:00',
                ],
            ],
        ];
    }

    private function requestSchedule(int $userId, string $startDate, string $endDate): TestResponse
    {
        return $this->call(Request::METHOD_GET, self::GET_EMPLOYEE_SCHEDULE_ENDPOINT_URL, [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'employeeId' => $userId,
        ]);
    }

    /**
     * @param mixed[] $schedule
     *
     * @return string[][]|null
     */
    private function findDaySchedule(string $day, array $schedule): ?array
    {
        foreach ($schedule as $daySchedule) {
            if ($daySchedule['day'] === $day) {
                return $daySchedule;
            }
        }

        return null;
    }

    private function assertResponseContainsSchedule(TestResponse $testResponse): void
    {
        $responseData = $testResponse->json();

        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('schedule', $responseData);

        $schedule = $responseData['schedule'];

        $this->assertIsArray($schedule);
        $this->assertNotCount(0, $schedule);

        foreach ($schedule as $daySchedule) {
            $this->assertDayScheduleIsValid($daySchedule);
        }
    }

    /**
     * @param mixed[] $daySchedule
     */
    private function assertDayScheduleIsValid(array $daySchedule): void
    {
        $this->assertArrayHasKey('day', $daySchedule);
        $this->assertArrayHasKey('timeRanges', $daySchedule);

        $day = $daySchedule['day'];
        $timeRanges = $daySchedule['timeRanges'];

        $this->assertIsString($day);
        $this->assertIsArray($timeRanges);
        $this->assertNotCount(0, $timeRanges);

        foreach ($timeRanges as $timeRange) {
            $this->assertTimeRangeIsValid($timeRange);
        }
    }

    /**
     * @param mixed[] $timeRange
     */
    private function assertTimeRangeIsValid(array $timeRange): void
    {
        $this->assertArrayHasKey('start', $timeRange);
        $this->assertArrayHasKey('end', $timeRange);

        $startTime = $timeRange['start'];
        $endTime = $timeRange['end'];

        $this->assertIsString($startTime);
        $this->assertIsString($endTime);
    }

    private function assertResponseContainsErrors(TestResponse $testResponse): void
    {
        $responseData = $testResponse->json();

        $this->assertArrayHasKey('errors', $responseData);

        $errors = $responseData['errors'];

        $this->assertIsArray($errors);
        $this->assertNotCount(0, $errors);
    }

    /**
     * @private
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../../../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
