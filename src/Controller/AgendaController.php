<?php

namespace App\Controller;

use App\Entity\Years;
use App\Entity\Months;
use App\Entity\Days;
use App\Entity\Slots;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AgendaController extends AbstractController
{
    #[Route('/agenda/view', name: 'app_agenda')]
public function agenda(Request $request, EntityManagerInterface $em): Response
{
    $yearNumber = (int) date('Y');
    $monthNumber = (int) date('n');
    $dayNumber = (int) date('j');

    // Récupération des paramètres AJAX
    $yearNumber = $request->query->getInt('year', $yearNumber);
    $monthNumber = $request->query->getInt('month', $monthNumber);
    $dayNumber = $request->query->getInt('day', $dayNumber);
    $view = $request->query->get('view', 'month'); // 'month' ou 'day'

    $yearRepo = $em->getRepository(Years::class);
    $year = $yearRepo->findOneBy(['number' => $yearNumber]);

    if (!$year) {
        throw $this->createNotFoundException("Année $yearNumber introuvable.");
    }

    $month = null;
    foreach ($year->getMonths() as $m) {
        if ($m->getNumber() === $monthNumber) {
            $month = $m;
            break;
        }
    }
    if (!$month) {
        throw $this->createNotFoundException("Mois $monthNumber introuvable.");
    }

    $today = (int) date('j');

    // Préparation navigation
    $prevMonth = $monthNumber - 1;
    $nextMonth = $monthNumber + 1;
    $prevYear = $nextYear = $yearNumber;
    if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
    if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

    // Si AJAX → vue "day" ou "month"
    if ($request->isXmlHttpRequest()) {
        if ($view === 'day') {
            $dayEntity = null;
            foreach ($month->getDays() as $d) {
                if ($d->getNumber() === $dayNumber) {
                    $dayEntity = $d;
                    break;
                }
            }
            if (!$dayEntity) {
                throw $this->createNotFoundException("Jour $dayNumber introuvable.");
            }

            return $this->json([
                'slotsHtml' => $this->renderView('agenda/_day_slots.html.twig', [
                    'date'  => new \DateTime("$yearNumber-$monthNumber-$dayNumber"),
                    'slots' => $dayEntity->getSlots(),
                ])
            ]);
        }

        // sinon → vue "month"
        return $this->json([
            'calendarHtml' => $this->renderView('agenda/_agenda_table.html.twig', [
                'month' => $month,
                'year'  => $year,
                'today' => $today,
            ]),
            'monthName'  => $month->getName(),
            'yearNumber' => $yearNumber,
            'prevMonth'  => $prevMonth,
            'prevYear'   => $prevYear,
            'nextMonth'  => $nextMonth,
            'nextYear'   => $nextYear,
        ]);
    }

    // Vue complète (premier chargement)
    return $this->render('agenda/agenda.html.twig', [
        'year'      => $year,
        'month'     => $month,
        'today'     => $today,
        'prevMonth' => $prevMonth,
        'prevYear'  => $prevYear,
        'nextMonth' => $nextMonth,
        'nextYear'  => $nextYear,
    ]);
}


    #[Route('/agenda/init/{yearNumber}', name: 'app_agenda_init')]
    public function initAgenda(int $yearNumber, EntityManagerInterface $em): Response
    {
        $yearRepo = $em->getRepository(Years::class);
        $monthRepo = $em->getRepository(Months::class);
        $dayRepo = $em->getRepository(Days::class);

        $year = $yearRepo->findOneBy(['number' => $yearNumber]);
        if (!$year) {
            $year = new Years();
            $year->setNumber($yearNumber);
            $em->persist($year);
        }

        $monthNames = [
            1 => "Janvier", 2 => "Février", 3 => "Mars", 4 => "Avril",
            5 => "Mai", 6 => "Juin", 7 => "Juillet", 8 => "Août",
            9 => "Septembre", 10 => "Octobre", 11 => "Novembre", 12 => "Décembre"
        ];

        for ($month = 1; $month <= 12; $month++) {
            $existingMonth = $monthRepo->findOneBy(['number' => $month, 'year' => $year]);

            if (!$existingMonth) {
                $existingMonth = new Months();
                $existingMonth->setNumber($month);
                $existingMonth->setName($monthNames[$month]);
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $yearNumber);
                $existingMonth->setSize($daysInMonth);
                $existingMonth->setYear($year);
                $em->persist($existingMonth);
            }

            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $yearNumber);

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $existingDay = $dayRepo->findOneBy(['number' => $day, 'month' => $existingMonth]);

                if (!$existingDay) {
                    $existingDay = new Days();
                    $existingDay->setNumber($day);
                    $existingDay->setMonth($existingMonth);

                    $date = new \DateTime("$yearNumber-$month-$day");
                    $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);
                    $formatter->setPattern('EEEE');
                    $existingDay->setName($formatter->format($date));

                    $em->persist($existingDay);
                }

                if ($existingDay->getSlots()->isEmpty()) {
                    $date = new \DateTime("$yearNumber-$month-$day");

                    for ($hour = 8; $hour < 12; $hour++) {
                        $begin = (clone $date)->setTime($hour, 0);
                        $end = (clone $begin)->modify('+1 hour');

                        $slot = new Slots();
                        $slot->setDay($existingDay);
                        $slot->setBegin($begin);
                        $slot->setEnd($end);
                        $slot->setAvailable(true);

                        $em->persist($slot);
                    }

                    for ($hour = 13; $hour < 17; $hour++) {
                        $begin = (clone $date)->setTime($hour, 30);
                        $end = (clone $begin)->modify('+1 hour');

                        $slot = new Slots();
                        $slot->setDay($existingDay);
                        $slot->setBegin($begin);
                        $slot->setEnd($end);
                        $slot->setAvailable(true);

                        $em->persist($slot);
                    }
                }
            }
        }

        $em->flush();

        return new Response("Agenda $yearNumber vérifié et complété avec succès !");
    }
}
