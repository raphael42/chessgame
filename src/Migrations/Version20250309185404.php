<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250309185404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('TRUNCATE TABLE `challenge_user`');

        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('TRUNCATE TABLE challenge');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');

        $this->addSql("INSERT INTO `challenge` (`id`, `ordering`, `title`, `subtitle`, `score_goal`, `description`, `fen`, `slug`) VALUES
            (1,	1,	'La tour',	'Elle se déplace sur une ligne droite',	1,	'Cliquer sur la tour et la déplacer jusqu\'à l\'étoile!',	'8/4*3/8/8/8/8/4R3/8 w - - 0 1',	'rook'),
            (2,	2,	'La tour',	'Elle se déplace sur une ligne droite',	2,	'Collectez toutes les étoiles !',	'8/2R5/8/2*3*1/8/8/8/8 w - - 0 1',	'rook'),
            (3,	3,	'La tour',	'Elle se déplace sur une ligne droite',	3,	'Moins vous effectuez de déplacements, plus vous gagnez de points !',	'8/8/8/8/*2R2*1/6*1/8/8 w - - 0 1',	'rook'),
            (4,	4,	'La tour',	'Elle se déplace sur une ligne droite',	5,	'Moins vous effectuez de déplacements, plus vous gagnez de points !',	'5**R/6**/8/8/8/8/8/6*2 w - - 0 1',	'rook'),
            (5,	5,	'La tour',	'Elle se déplace sur une ligne droite',	4,	'Utiliser 2 Tours pour collecter toutes les étoiles !',	'8/1R4*1/8/8/*2R3*/6*1/8/8 w - - 0 1',	'rook'),
            (6,	6,	'La tour',	'Elle se déplace sur une ligne droite',	7,	'Utiliser 2 Tours pour collecter toutes les étoiles !',	'8/1*3**1/8/3*4/6*1/5R2/5*2/R2*4 w - - 0 1',	'rook'),
            (7,	1,	'Le pion',	'Il se déplace uniquement vers l\'avant',	4,	'Les pions avancent d\'une case vers l\'avant uniquement.<br>Mais, lorsqu\'ils atteignent le bord opposé de l\'échiquier, ils deviennent une pièce plus puissante : c\'est une promotion !',	'8/8/8/P7/8/5*2/8/8 w - - 0 1',	'pawn'),
            (8,	2,	'Le pion',	'Il se déplace uniquement vers l\'avant',	8,	'La plupart du temps, la promotion en Dame est la plus avantageuse.<br>Mais, parfois, une promotion en Cavalier peut s\'avérer utile!',	'*7/3*4/1*6/4*P2/2*5/8/8/8 w - - 0 1',	'pawn'),
            (9,	3,	'Le pion',	'Il se déplace uniquement vers l\'avant',	4,	'Les pions se déplacent vers l\'avant, mais capturent en diagonale !',	'8/3*4/2*5/3*4/8/4P3/8/8 w - - 0 1',	'pawn'),
            (10,	4,	'Le pion',	'Il se déplace uniquement vers l\'avant',	8,	'Capturez, puis effectuez une promotion !',	'8/2*5/1***4/8/1**5/1P6/8/8 w - - 0 1',	'pawn'),
            (11,	5,	'Le pion',	'Il se déplace uniquement vers l\'avant',	8,	'Capturez, puis effectuez une promotion !',	'2*5/3*4/1*2*3/1*1*4/2*5/3P4/8/8 w - - 0 1',	'pawn'),
            (12,	6,	'Le pion',	'Il se déplace uniquement vers l\'avant',	7,	'Utilisez tous vos pions !<br>Inutile d\'effectuer des promotions.',	'8/8/8/1**1*3/3*2*1/P1PP3P/8/8 w - - 0 1',	'pawn'),
            (13,	7,	'Le pion',	'Il se déplace uniquement vers l\'avant',	3,	'Un pion sur sa case de départ peut se déplacer initialement de 2 cases d\'un coup !',	'8/8/3*4/8/8/8/4P3/8 w - - 0 1',	'pawn'),
            (14,	8,	'Le pion',	'Il se déplace uniquement vers l\'avant',	9,	'Collecter toutes les étoiles ! Inutile d\'effectuer des promotions.',	'8/8/8/2****2/4*3/3*4/2PPPP2/8 w - - 0 1',	'pawn'),
            (15,	1,	'Le fou',	'Il se déplace en diagonale',	2,	'Collectez toutes les étoiles !',	'6*1/8/8/3*4/8/5B2/8/8 w - - 0 1',	'bishop'),
            (16,	2,	'Le fou',	'Il se déplace en diagonale',	6,	'Moins vous effectuez de déplacements, plus vous gagnez de points !',	'8/8/8/1*6/8/1B1*4/*3*3/1*1*4 w - - 0 1',	'bishop'),
            (17,	3,	'Le fou',	'Il se déplace en diagonale',	6,	'Collectez toutes les étoiles !',	'8/6*1/1*5*/8/3B4/4*3/8/*1*5 w - - 0 1',	'bishop'),
            (18,	4,	'Le fou',	'Il se déplace en diagonale',	6,	'Collectez toutes les étoiles !',	'8/8/8/8/*1B5/1*1*4/2*1*3/1*6 w - - 0 1',	'bishop'),
            (19,	5,	'Le fou',	'Il se déplace en diagonale',	6,	'Un fou de cases blanches,<br>Un fou de cases noires.<br>Vous avez besoin des deux !',	'8/8/8/3**3/3**3/3**3/8/2B2B2 w - - 0 1',	'bishop'),
            (20,	6,	'Le fou',	'Il se déplace en diagonale',	8,	'Un fou de cases blanches,<br>Un fou de cases noires.<br>Vous avez besoin des deux !',	'6*1/3B*2*/5*2/5*2/7*1/*1B5/2*5/8 w - - 0 1',	'bishop'),
            (21,	1,	'Le Cavalier',	'Il se déplace en forme de L',	2,	'Les Cavaliers possèdent une façon élégante de sauter partout !',	'8/3*4/8/2*5/4N3/8/8/8 w - - 0 1',	'knight'),
            (22,	2,	'Le Cavalier',	'Il se déplace en forme de L',	7,	'Collectez toutes les étoiles !',	'7*/5*2/8/6*1/3*4/2*2*2/4*3/1N6 w - - 0 1',	'knight'),
            (23,	3,	'Le Cavalier',	'Il se déplace en forme de L',	5,	'Collectez toutes les étoiles !',	'8/2N*4/1*2*3/3*4/5*2/8/8/8 w - - 0 1',	'knight'),
            (24,	4,	'Le Cavalier',	'Il se déplace en forme de L',	9,	'Les Cavaliers peuvent sauter par dessus les obstacles pour collecter les étoiles !',	'8/8/8/4***1/4*N*1/4***1/8/8 w - - 0 1',	'knight'),
            (25,	5,	'Le Cavalier',	'Il se déplace en forme de L',	6,	'Collectez toutes les étoiles !',	'8/8/6*1/8/4**2/2*N4/4**2/8 w - - 0 1',	'knight'),
            (26,	6,	'Le Cavalier',	'Il se déplace en forme de L',	9,	'Collectez toutes les étoiles !',	'2*5/2N1*3/2*5/1*1*1*2/1*1*4/4*3/8/8 w - - 0 1',	'knight'),
            (27,	1,	'La dame',	'Dame = tour + fou',	2,	'Collectez toutes les étoiles !',	'1*6/8/8/4*3/8/8/4Q3/8 w - - 0 1',	'queen'),
            (28,	2,	'La dame',	'Dame = tour + fou',	4,	'Collectez toutes les étoiles !',	'5*2/8/8/8/3Q4/*6*/5*2/8 w - - 0 1',	'queen'),
            (29,	3,	'La dame',	'Dame = tour + fou',	6,	'Collectez toutes les étoiles !',	'5*2/8/3*3*/8/2Q5/*5*1/8/5*2 w - - 0 1',	'queen'),
            (30,	5,	'La dame',	'Dame = tour + fou',	7,	'Collectez toutes les étoiles !',	'6*1/6Q1/8/1*5*/8/3*4/*6*/6*1 w - - 0 1',	'queen'),
            (31,	6,	'La dame',	'Dame = tour + fou',	8,	'Collectez toutes les étoiles !',	'6*1/8/*4**1/8/7*/8/5*2/3*Q2* w - - 0 1',	'queen'),
            (32,	1,	'Le roi',	'La pièce la plus importante',	3,	'Le Roi se déplace lentement',	'8/8/4*3/8/8/3K4/8/8 w - - 0 1',	'king'),
            (33,	2,	'Le roi',	'La pièce la plus importante',	4,	'Collectez toutes les étoiles !',	'8/8/8/8/8/3**3/2*1*3/4K3 w - - 0 1',	'king'),
            (35,	3,	'Le roi',	'La pièce la plus importante',	8,	'Collectez toutes les étoiles !',	'8/8/3*4/1**1K3/6*1/4**2/8/8 w - - 0 1',	'king'),
            (36,	1,	'Capturer',	'Prenez les pièces adverses',	2,	'Prenez les pièces noires !',	'8/2p2p2/8/8/8/2R5/8/8 w - - 0 1',	'capture'),
            (37,	2,	'Capturer',	'Prenez les pièces adverses',	2,	'Prenez les pièces noires !<br>Et ne perdez pas les vôtres.',	'8/2r2p2/8/8/5Q2/8/8/8 w - - 0 1',	'capture'),
            (38,	3,	'Capturer',	'Prenez les pièces adverses',	5,	'Prenez les pièces noires !<br>Et ne perdez pas les vôtres.',	'8/5r2/8/1r3p2/8/3B4/8/8 w - - 0 1',	'capture'),
            (39,	4,	'Capturer',	'Prenez les pièces adverses',	7,	'Prenez les pièces noires !<br>Et ne perdez pas les vôtres.',	'8/5b2/5p2/3n2p1/8/6Q1/8/8 w - - 0 1',	'capture'),
            (40,	5,	'Capturer',	'Prenez les pièces adverses',	6,	'Prenez les pièces noires !<br>Et ne perdez pas les vôtres.',	'8/3b4/2p2q2/8/3p1N2/8/8/8 w - - 0 1',	'capture'),
            (65,	1,	'Protéger',	'Assurez la sécurité de vos pièces',	1,	'Vous êtes attaqué ! Fuyez la menace !',	'8/8/8/4bb2/8/8/P2P4/R2K4 w - - 0 1',	'protect'),
            (66,	2,	'Protéger',	'Assurez la sécurité de vos pièces',	1,	'Vous êtes attaqué ! Fuyez la menace !',	'8/8/2q2N2/8/8/8/8/8 w - - 0 1',	'protect'),
            (67,	3,	'Protéger',	'Assurez la sécurité de vos pièces',	1,	'Il n\'y a pas d\'échappatoire, mais vous pouvez vous défendre !',	'8/N2q4/8/8/8/8/6R1/8 w - - 0 1',	'protect'),
            (68,	4,	'Protéger',	'Assurez la sécurité de vos pièces',	1,	'Il n\'y a pas d\'échappatoire, mais vous pouvez vous défendre !',	'8/8/1Bq5/8/2P5/8/8/8 w - - 0 1',	'protect'),
            (69,	5,	'Protéger',	'Assurez la sécurité de vos pièces',	1,	'Il n\'y a pas d\'échappatoire, mais vous pouvez vous défendre !',	'1r6/8/5b2/8/8/5N2/P2P4/R1B5 w - - 0 1',	'protect'),
            (70,	6,	'Protéger',	'Assurez la sécurité de vos pièces',	1,	'Ne laissez pas l\'adversaire prendre une pièce non défendue !',	'8/1b6/8/8/8/3P2P1/5NRP/r7 w - - 0 1',	'protect'),
            (71,	7,	'Protéger',	'Assurez la sécurité de vos pièces',	1,	'Ne laissez pas l\'adversaire prendre une pièce non défendue !',	'rr6/3q4/4n3/4P1B1/7P/P7/1B1N1PP1/R5K1 w - - 0 1',	'protect'),
            (72,	8,	'Protéger',	'Assurez la sécurité de vos pièces',	1,	'Ne laissez pas l\'adversaire prendre une pièce non défendue !',	'8/3q4/8/1N3R2/8/2PB4/8/8 w - - 0 1',	'protect'),
            (119,	1,	'Combat',	'Capturez et défendez les pièces',	3,	'Prenez les pièces noires !<br>Et ne perdez pas les vôtres.',	'8/8/8/8/P2r4/6B1/8/8 w - - 0 1',	'fight'),
            (120,	2,	'Combat',	'Capturez et défendez les pièces',	4,	'Prenez les pièces noires !<br>Et ne perdez pas les vôtres.',	'2r5/8/3b4/2P5/8/1P6/2B5/8 w - - 0 1',	'fight'),
            (121,	3,	'Combat',	'Capturez et défendez les pièces',	4,	'Prenez les pièces noires !<br>Et ne perdez pas les vôtres.',	'1r6/8/5n2/3P4/4P1P1/1Q6/8/8 w - - 0 1',	'fight'),
            (122,	4,	'Combat',	'Capturez et défendez les pièces',	4,	'Prenez les pièces noires !<br>Et ne perdez pas les vôtres.',	'2r5/8/3N4/5b2/8/8/PPP5/8 w - - 0 1',	'fight'),
            (123,	5,	'Combat',	'Capturez et défendez les pièces',	8,	'Prenez les pièces noires !<br>Et ne perdez pas les vôtres.',	'8/6q1/8/4P1P1/8/4B3/r2P2N1/8 w - - 0 1',	'fight'),
            (124,	1,	'Échec en un coup',	'Attaquez le Roi adverse',	1,	'Menacez le Roi adverse en un coup !',	'4k3/8/2b5/8/8/8/8/R7 w - - 0 1',	'check'),
            (125,	2,	'Échec en un coup',	'Attaquez le Roi adverse',	1,	'Menacez le Roi adverse en un coup !',	'8/8/4k3/3n4/8/1Q6/8/8 w - - 0 1',	'check'),
            (126,	3,	'Échec en un coup',	'Attaquez le Roi adverse',	1,	'Menacez le Roi adverse en un coup !',	'3qk3/1pp5/3p4/4p3/8/3B4/6r1/8 w - - 0 1',	'check'),
            (127,	4,	'Échec en un coup',	'Attaquez le Roi adverse',	1,	'Menacez le Roi adverse en un coup !',	'2r2q2/2n5/8/4k3/8/2N1P3/3P2B1/8 w - - 0 1',	'check'),
            (128,	5,	'Échec en un coup',	'Attaquez le Roi adverse',	1,	'Menacez le Roi adverse en un coup !',	'8/2b1q2n/1ppk4/2N5/8/8/8/8 w - - 0 1',	'check'),
            (129,	6,	'Échec en un coup',	'Attaquez le Roi adverse',	1,	'Menacez le Roi adverse en un coup !',	'6R1/1k3r2/8/4Q3/8/2n5/8/8 w - - 0 1',	'check'),
            (130,	7,	'Échec en un coup',	'Attaquez le Roi adverse',	1,	'Menacez le Roi adverse en un coup !',	'7r/4k3/8/3n4/4N3/8/2R5/4Q3 w - - 0 1',	'check'),
            (131,	1,	'Parer un échec',	'Défendez votre Roi',	1,	'Faites fuir votre Roi !',	'8/8/8/4q3/8/8/8/4K3 w - - 0 1',	'outcheck'),
            (132,	2,	'Parer un échec',	'Défendez votre Roi',	1,	'Faites fuir votre Roi !',	'8/2n5/5b2/8/2K5/8/2q5/8 w - - 0 1',	'outcheck'),
            (133,	3,	'Parer un échec',	'Défendez votre Roi',	1,	'Le Roi ne peut pas s\'échapper, mais vous pouvez parer l\'attaque !',	'8/7r/6r1/8/R7/7K/8/8 w - - 0 1',	'outcheck'),
            (134,	4,	'Parer un échec',	'Défendez votre Roi',	1,	'Vous pouvez parer l\'échec en prenant la pièce attaquante.',	'8/8/8/3b4/8/4N3/KBn5/1R6 w - - 0 1',	'outcheck'),
            (135,	5,	'Parer un échec',	'Défendez votre Roi',	1,	'Ce Cavalier donne un échec à travers vos défenses !',	'4q3/8/8/8/8/5nb1/3PPP2/3QKBNr w - - 0 1',	'outcheck'),
            (136,	6,	'Parer un échec',	'Défendez votre Roi',	1,	'Fuyez avec le Roi ou bloquez l\'attaque !',	'8/8/7p/2q5/5n2/1N1KP2r/3R4/8 w - - 0 1',	'outcheck'),
            (137,	7,	'Parer un échec',	'Défendez votre Roi',	1,	'Fuyez avec le Roi ou bloquez l\'attaque !',	'8/6b1/8/8/q4P2/2KN4/3P4/8 w - - 0 1',	'outcheck'),
            (138,	1,	'Mat en un coup',	'Matez le Roi adverse',	1,	'Attaquez le Roi de votre adversaire de manière qu\'il ne puisse pas être défendu !',	'3qk3/3ppp2/8/8/2B5/5Q2/8/8 w - - 0 1',	'checkmate'),
            (139,	2,	'Mat en un coup',	'Matez le Roi adverse',	1,	'Attaquez le Roi de votre adversaire de manière qu\'il ne puisse pas être défendu !',	'6rk/6pp/7P/6N1/8/8/8/8 w - - 0 1',	'checkmate'),
            (140,	3,	'Mat en un coup',	'Matez le Roi adverse',	1,	'Attaquez le Roi de votre adversaire de manière qu\'il ne puisse pas être défendu !',	'R7/8/7k/2r5/5n2/8/6Q1/8 w - - 0 1',	'checkmate'),
            (141,	4,	'Mat en un coup',	'Matez le Roi adverse',	1,	'Attaquez le Roi de votre adversaire de manière qu\'il ne puisse pas être défendu !',	'2rb4/2q5/5N2/1Q6/8/8/8/8 w - - 0 1',	'checkmate'),
            (142,	5,	'Mat en un coup',	'Matez le Roi adverse',	1,	'Attaquez le Roi de votre adversaire de manière qu\'il ne puisse pas être défendu !',	'1r2kb2/ppB1p3/2P2p2/2p1N3/B7/8/8/3R4 w - - 0 1',	'checkmate'),
            (143,	6,	'Mat en un coup',	'Matez le Roi adverse',	1,	'Attaquez le Roi de votre adversaire de manière qu\'il ne puisse pas être défendu !',	'8/pk1N4/n7/b7/6B1/1r3b2/8/1RR5 w - - 0 1',	'checkmate'),
            (144,	7,	'Mat en un coup',	'Matez le Roi adverse',	1,	'Attaquez le Roi de votre adversaire de manière qu\'il ne puisse pas être défendu !',	'r1b5/ppp5/2N2kpN/5q2/8/Q7/8/4B3 w - - 0 1',	'checkmate');"
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
