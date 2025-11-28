-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 22, 2025 at 03:56 PM
-- Server version: 5.7.44
-- PHP Version: 8.1.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `atticagoldnew_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `asset`
--

CREATE TABLE `asset` (
  `id` int(11) NOT NULL,
  `branchId` varchar(255) NOT NULL,
  `userName` varchar(255) NOT NULL,
  `serialNumber` varchar(255) NOT NULL,
  `keyboard` varchar(255) NOT NULL,
  `displaySize` varchar(200) NOT NULL,
  `processor` varchar(200) NOT NULL,
  `ram` varchar(100) NOT NULL,
  `mouse` varchar(100) NOT NULL,
  `headphone` varchar(100) NOT NULL,
  `systemName` varchar(255) NOT NULL,
  `storage` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(100) NOT NULL,
  `empId` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `branchId` varchar(8) NOT NULL,
  `branch` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `photo` varchar(255) NOT NULL,
  `vmempID` varchar(50) NOT NULL DEFAULT '',
  `vmStatus` tinyint(3) NOT NULL DEFAULT '0',
  `vmTime` time NOT NULL,
  `lastlogin` varchar(250) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL,
  `status` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bankdetails`
--

CREATE TABLE `bankdetails` (
  `ID` int(11) NOT NULL,
  `customerId` varchar(20) NOT NULL,
  `billID` varchar(20) NOT NULL,
  `accountHolder` varchar(100) NOT NULL,
  `relationship` varchar(50) NOT NULL,
  `bank` varchar(255) NOT NULL,
  `branch` varchar(255) NOT NULL,
  `account` varchar(30) NOT NULL,
  `ifsc` varchar(50) NOT NULL,
  `Bproof` varchar(100) NOT NULL DEFAULT '',
  `date` date NOT NULL,
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `branch`
--

CREATE TABLE `branch` (
  `id` int(100) NOT NULL,
  `branchId` varchar(255) NOT NULL,
  `branchName` varchar(255) NOT NULL,
  `branchArea` varchar(255) NOT NULL,
  `addr` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` int(6) DEFAULT NULL,
  `officeContact` varchar(255) NOT NULL,
  `branchManager` varchar(255) NOT NULL,
  `phone` bigint(13) NOT NULL,
  `email` varchar(255) NOT NULL,
  `gst` varchar(255) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `url` varchar(100) NOT NULL,
  `Status` int(1) NOT NULL,
  `priceId` varchar(255) NOT NULL,
  `grade` varchar(10) NOT NULL DEFAULT 'C',
  `rating` int(10) NOT NULL DEFAULT '0',
  `openDate` varchar(20) NOT NULL,
  `closeDate` varchar(20) NOT NULL,
  `renewal_date` varchar(100) NOT NULL,
  `renewal_status` varchar(100) NOT NULL,
  `ws_access` tinyint(4) NOT NULL DEFAULT '0',
  `meet` varchar(255) NOT NULL,
  `ezviz_vc` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `closing`
--

CREATE TABLE `closing` (
  `closingID` int(100) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `totalAmount` varchar(255) NOT NULL,
  `transactions` varchar(255) NOT NULL,
  `transactionAmount` varchar(255) NOT NULL,
  `expenses` varchar(255) NOT NULL,
  `balance` varchar(255) NOT NULL,
  `grossWG` varchar(255) NOT NULL,
  `netWG` varchar(100) NOT NULL,
  `grossAG` varchar(100) NOT NULL,
  `netAG` varchar(100) NOT NULL,
  `grossWS` varchar(100) NOT NULL,
  `netWS` varchar(100) NOT NULL,
  `grossAS` varchar(100) NOT NULL,
  `netAS` varchar(100) NOT NULL,
  `one` varchar(100) NOT NULL,
  `two` varchar(100) NOT NULL,
  `three` varchar(100) NOT NULL,
  `four` varchar(100) NOT NULL,
  `five` varchar(100) NOT NULL,
  `six` varchar(100) NOT NULL,
  `seven` varchar(100) NOT NULL,
  `eight` varchar(100) NOT NULL,
  `nine` varchar(100) NOT NULL,
  `ten` varchar(100) NOT NULL,
  `total` varchar(255) NOT NULL,
  `diff` varchar(255) NOT NULL,
  `branchId` varchar(255) NOT NULL,
  `open` varchar(255) NOT NULL,
  `forward` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `id` int(100) NOT NULL,
  `customerId` varchar(13) NOT NULL,
  `name` varchar(255) NOT NULL,
  `gender` varchar(6) NOT NULL,
  `dob` varchar(10) NOT NULL,
  `mobile` bigint(13) NOT NULL,
  `amobile` varchar(11) DEFAULT NULL,
  `paline` varchar(255) NOT NULL,
  `pcity` varchar(30) NOT NULL,
  `pstate` varchar(30) NOT NULL,
  `ppin` bigint(13) NOT NULL,
  `pland` varchar(255) NOT NULL,
  `plocality` varchar(255) NOT NULL,
  `caline` varchar(255) NOT NULL,
  `ccity` varchar(30) NOT NULL,
  `cstate` varchar(30) NOT NULL,
  `cpin` bigint(13) NOT NULL,
  `cland` varchar(255) NOT NULL,
  `clocality` varchar(255) NOT NULL,
  `resident` varchar(30) NOT NULL,
  `idProof` varchar(255) NOT NULL,
  `addProof` varchar(255) NOT NULL,
  `idFile` varchar(255) NOT NULL,
  `addFile` varchar(255) NOT NULL,
  `idNumber` varchar(255) NOT NULL,
  `addNumber` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `customerImage` varchar(255) NOT NULL,
  `time` time NOT NULL,
  `relation` varchar(255) NOT NULL,
  `rcontact` varchar(255) NOT NULL,
  `cusThump` varchar(244) NOT NULL DEFAULT '',
  `custSign` varchar(244) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `customerinfo`
--

CREATE TABLE `customerinfo` (
  `id` int(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `branchId` varchar(10) NOT NULL,
  `billId` varchar(10) NOT NULL,
  `idNum` varchar(255) NOT NULL,
  `addNum` varchar(255) NOT NULL,
  `detail` varchar(255) NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `approval` varchar(10) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `customer_data`
--

CREATE TABLE `customer_data` (
  `id` int(11) NOT NULL,
  `mobile` varchar(13) NOT NULL,
  `idFile` varchar(255) NOT NULL,
  `addFile` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `id` int(100) NOT NULL,
  `empId` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `mailId` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `rating` int(10) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `enquiry`
--

CREATE TABLE `enquiry` (
  `id` int(10) NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `mobile` varchar(15) CHARACTER SET latin1 NOT NULL,
  `type` varchar(250) CHARACTER SET latin1 NOT NULL,
  `state` varchar(250) CHARACTER SET latin1 NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `status` varchar(250) CHARACTER SET latin1 NOT NULL,
  `remarks` varchar(250) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL,
  `comments` varchar(255) CHARACTER SET latin1 NOT NULL,
  `updateDate` varchar(255) CHARACTER SET latin1 NOT NULL,
  `followup` varchar(25) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL,
  `device` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `everycustomer`
--

CREATE TABLE `everycustomer` (
  `Id` int(10) NOT NULL,
  `customer` varchar(50) NOT NULL,
  `contact` bigint(10) NOT NULL,
  `type` varchar(255) NOT NULL,
  `idnumber` varchar(255) NOT NULL,
  `branch` varchar(20) NOT NULL,
  `image` varchar(100) NOT NULL,
  `quotation` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `status` varchar(20) NOT NULL,
  `status_remark` varchar(50) NOT NULL DEFAULT '',
  `remark` text NOT NULL,
  `block_counter` int(5) NOT NULL DEFAULT '0',
  `extra` varchar(255) NOT NULL DEFAULT '',
  `reg_type` varchar(50) NOT NULL DEFAULT '',
  `agent` varchar(255) NOT NULL,
  `agent_time` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `expense`
--

CREATE TABLE `expense` (
  `id` int(100) NOT NULL,
  `branchCode` varchar(255) NOT NULL,
  `employeeId` varchar(255) NOT NULL,
  `particular` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `file1` varchar(255) NOT NULL,
  `amount` varchar(255) NOT NULL,
  `status` varchar(25) NOT NULL,
  `date` varchar(255) NOT NULL,
  `time` varchar(255) NOT NULL,
  `remarks` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fraud`
--

CREATE TABLE `fraud` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(11) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'NA',
  `idnumber` varchar(255) NOT NULL DEFAULT 'NA',
  `date` varchar(255) NOT NULL,
  `branchId` varchar(100) NOT NULL DEFAULT '',
  `reason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `fund`
--

CREATE TABLE `fund` (
  `id` int(100) NOT NULL,
  `available` varchar(255) NOT NULL,
  `request` int(100) NOT NULL,
  `type` varchar(255) NOT NULL,
  `branch` varchar(255) NOT NULL,
  `number` varchar(255) NOT NULL,
  `holder` varchar(255) NOT NULL,
  `ifsc` varchar(255) NOT NULL,
  `bankBranch` varchar(255) NOT NULL,
  `bankName` varchar(255) NOT NULL,
  `chequeDate` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `customerName` varchar(255) NOT NULL,
  `customerMobile` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `gold`
--

CREATE TABLE `gold` (
  `id` int(100) NOT NULL,
  `cash` varchar(255) NOT NULL,
  `transferRate` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `date` varchar(255) NOT NULL,
  `time` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `brand` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `inventory_received` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `inventory_shipped` int(11) NOT NULL,
  `supplier` varchar(200) NOT NULL DEFAULT '',
  `purchase_date` date DEFAULT NULL,
  `remarks` text NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `last_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `id` int(100) NOT NULL,
  `product_id` varchar(255) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `from_branch` varchar(200) NOT NULL,
  `to_branch` varchar(255) NOT NULL,
  `date` varchar(255) NOT NULL,
  `time` time NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `delivery_type` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL DEFAULT '1',
  `last_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `it_issue`
--

CREATE TABLE `it_issue` (
  `id` int(10) NOT NULL,
  `branchId` varchar(20) NOT NULL,
  `branchName` varchar(100) NOT NULL,
  `empId` varchar(25) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL,
  `username` varchar(25) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL,
  `issueType` varchar(40) NOT NULL,
  `priority` varchar(15) NOT NULL,
  `remarks` varchar(300) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `contact` varchar(13) NOT NULL,
  `anydesk` varchar(20) NOT NULL,
  `status` varchar(10) NOT NULL,
  `itname` varchar(32) NOT NULL,
  `rslvDate` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `job`
--

CREATE TABLE `job` (
  `id` int(10) NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `mobile` varchar(10) CHARACTER SET latin1 NOT NULL,
  `email` varchar(255) CHARACTER SET latin1 NOT NULL,
  `location` varchar(255) CHARACTER SET latin1 NOT NULL,
  `resume` varchar(255) CHARACTER SET latin1 NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `status` varchar(255) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `language`
--

CREATE TABLE `language` (
  `id` int(11) NOT NULL,
  `language` varchar(255) NOT NULL,
  `state` varchar(55) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `loginotp`
--

CREATE TABLE `loginotp` (
  `id` int(9) NOT NULL,
  `branch` varchar(12) NOT NULL,
  `empid` varchar(10) NOT NULL,
  `otp` int(6) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(9) NOT NULL,
  `type` varchar(20) NOT NULL,
  `username` varchar(20) NOT NULL,
  `ip` varchar(60) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mails`
--

CREATE TABLE `mails` (
  `id` int(11) NOT NULL,
  `fromBranch` varchar(255) NOT NULL,
  `toBranch` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `file` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `userType` varchar(255) NOT NULL,
  `flag` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `misc`
--

CREATE TABLE `misc` (
  `id` int(11) NOT NULL,
  `purpose` varchar(100) NOT NULL,
  `day` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `id` int(255) NOT NULL,
  `sender` varchar(255) NOT NULL,
  `receiver` varchar(255) NOT NULL,
  `branch` varchar(255) NOT NULL,
  `info` varchar(255) NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ornament`
--

CREATE TABLE `ornament` (
  `ornamentId` int(100) NOT NULL,
  `billId` varchar(9) NOT NULL,
  `employeeId` varchar(9) NOT NULL,
  `metal` varchar(255) NOT NULL,
  `pieces` int(10) NOT NULL,
  `type` varchar(255) NOT NULL,
  `typeInfo` varchar(255) NOT NULL DEFAULT '',
  `weight` varchar(255) NOT NULL,
  `sWaste` varchar(255) NOT NULL,
  `reading` varchar(255) NOT NULL,
  `purity` varchar(255) NOT NULL,
  `nine` varchar(255) NOT NULL,
  `gross` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `rate` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `otp`
--

CREATE TABLE `otp` (
  `otpid` int(100) NOT NULL,
  `branchId` varchar(250) DEFAULT NULL,
  `customerName` text NOT NULL,
  `mobile` bigint(13) NOT NULL,
  `message` varchar(255) NOT NULL,
  `otp` varchar(255) NOT NULL,
  `date` varchar(255) NOT NULL,
  `time` varchar(255) NOT NULL,
  `flag` tinyint(4) NOT NULL DEFAULT '0',
  `employee_id` varchar(100) DEFAULT '',
  `remarks` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pledge_bill`
--

CREATE TABLE `pledge_bill` (
  `id` int(255) NOT NULL,
  `billId` int(255) NOT NULL,
  `invoiceId` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `kyc1` varchar(255) NOT NULL,
  `kyc2` varchar(255) NOT NULL,
  `customerImage` varchar(255) CHARACTER SET latin1 NOT NULL,
  `ornamentImage` varchar(255) CHARACTER SET latin1 NOT NULL,
  `grossW` float NOT NULL,
  `stoneW` float NOT NULL,
  `amount` float NOT NULL,
  `rate` float NOT NULL,
  `rateAmount` float NOT NULL,
  `branchId` varchar(20) NOT NULL,
  `empId` varchar(20) NOT NULL,
  `empName` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pledge_fund`
--

CREATE TABLE `pledge_fund` (
  `id` int(255) NOT NULL,
  `billId` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `contact` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `paidamount` float NOT NULL,
  `status` varchar(255) NOT NULL,
  `branchId` varchar(255) NOT NULL,
  `empId` varchar(20) CHARACTER SET utf8mb4 NOT NULL,
  `empName` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pledge_ornament`
--

CREATE TABLE `pledge_ornament` (
  `id` int(255) NOT NULL,
  `invoiceId` varchar(255) NOT NULL,
  `ornamentType` varchar(255) NOT NULL,
  `count` int(10) NOT NULL,
  `grossW` float NOT NULL,
  `stoneW` float NOT NULL,
  `purity` float NOT NULL,
  `amount` float NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_branch_data`
--

CREATE TABLE `quiz_branch_data` (
  `id` int(100) NOT NULL,
  `branchId` varchar(10) NOT NULL,
  `empId` varchar(10) NOT NULL,
  `qid` varchar(16) NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_schedule`
--

CREATE TABLE `quiz_schedule` (
  `id` int(100) NOT NULL,
  `qid` varchar(16) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `releasedata`
--

CREATE TABLE `releasedata` (
  `rid` int(11) NOT NULL,
  `releaseID` int(10) NOT NULL,
  `BranchId` varchar(10) NOT NULL,
  `customerId` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` bigint(11) NOT NULL,
  `relPlaceType` varchar(10) NOT NULL,
  `relPlace` varchar(100) NOT NULL,
  `relDoc1` varchar(40) NOT NULL,
  `relDoc2` varchar(40) NOT NULL,
  `relDoc3` varchar(40) NOT NULL,
  `type` varchar(10) NOT NULL,
  `amount` int(10) NOT NULL,
  `relCash` int(10) NOT NULL,
  `relIMPS` int(10) NOT NULL,
  `relWith` varchar(10) NOT NULL,
  `pledgeSlips` int(5) NOT NULL,
  `bankName` varchar(100) NOT NULL,
  `branchName` varchar(100) NOT NULL,
  `accountHolder` varchar(100) NOT NULL,
  `relationship` varchar(20) NOT NULL,
  `loanAccNo` varchar(255) NOT NULL,
  `accountNo` varchar(50) NOT NULL,
  `IFSC` varchar(20) NOT NULL,
  `bProof` varchar(100) NOT NULL,
  `cProof` varchar(100) NOT NULL,
  `relGrossW` int(10) NOT NULL,
  `relNetW` int(10) NOT NULL,
  `relPurity` int(10) NOT NULL,
  `TEempId` varchar(100) NOT NULL,
  `TEcash` int(100) NOT NULL,
  `status` varchar(20) NOT NULL,
  `flag` int(100) NOT NULL DEFAULT '0',
  `date` date NOT NULL,
  `time` time NOT NULL,
  `remarks` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `renewal`
--

CREATE TABLE `renewal` (
  `id` int(100) NOT NULL,
  `branchId` varchar(20) NOT NULL,
  `internet` varchar(20) NOT NULL,
  `ISP_provider` varchar(255) NOT NULL,
  `Paid_By` varchar(10) NOT NULL,
  `shop_license` varchar(20) NOT NULL,
  `shop_license_validity` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `trans`
--

CREATE TABLE `trans` (
  `id` int(11) NOT NULL,
  `customerId` varchar(30) NOT NULL,
  `billId` varchar(9) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(13) NOT NULL,
  `billCount` varchar(255) NOT NULL,
  `releases` varchar(255) NOT NULL,
  `ple` varchar(255) NOT NULL,
  `grossW` varchar(255) NOT NULL,
  `netW` varchar(255) NOT NULL,
  `netA` varchar(255) NOT NULL,
  `grossA` varchar(255) NOT NULL,
  `amountPaid` varchar(255) NOT NULL,
  `rate` int(20) DEFAULT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `branchId` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `comm` varchar(255) NOT NULL,
  `margin` varchar(255) NOT NULL,
  `purity` varchar(255) NOT NULL,
  `price` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `metal` varchar(255) NOT NULL,
  `sta` varchar(255) NOT NULL,
  `staDate` varchar(10) NOT NULL,
  `flag` varchar(255) NOT NULL,
  `kyc` varchar(100) NOT NULL,
  `ophoto` varchar(100) NOT NULL,
  `remarks` varchar(50) NOT NULL,
  `paymentType` varchar(20) NOT NULL,
  `cashA` int(20) NOT NULL,
  `impsA` int(20) NOT NULL,
  `releaseID` int(10) NOT NULL,
  `relDate` varchar(20) NOT NULL,
  `packetNo` varchar(255) NOT NULL,
  `approvetime` varchar(20) NOT NULL,
  `imps_empid` varchar(20) NOT NULL,
  `impstime` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `trare`
--

CREATE TABLE `trare` (
  `id` int(100) NOT NULL,
  `avai` varchar(255) NOT NULL,
  `transferAmount` varchar(255) NOT NULL,
  `branchTo` varchar(255) NOT NULL,
  `branchId` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(100) NOT NULL,
  `type` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `employeeId` varchar(255) NOT NULL,
  `branch` varchar(255) NOT NULL,
  `agent` varchar(20) NOT NULL,
  `date` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL DEFAULT '',
  `language` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `vmagent`
--

CREATE TABLE `vmagent` (
  `id` int(11) NOT NULL,
  `agentId` varchar(255) NOT NULL,
  `branch` varchar(255) NOT NULL,
  `language` varchar(255) NOT NULL,
  `grade` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `vm_log`
--

CREATE TABLE `vm_log` (
  `id` int(11) NOT NULL,
  `empId` varchar(20) NOT NULL,
  `branchId` varchar(255) NOT NULL,
  `date` varchar(20) NOT NULL,
  `time` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `walkin`
--

CREATE TABLE `walkin` (
  `id` int(7) NOT NULL,
  `name` varchar(50) NOT NULL,
  `mobile` varchar(13) NOT NULL,
  `gold` varchar(20) NOT NULL,
  `havingG` varchar(20) NOT NULL,
  `metal` varchar(80) NOT NULL,
  `issue` varchar(50) NOT NULL,
  `gwt` varchar(9) NOT NULL DEFAULT '',
  `nwt` int(100) NOT NULL DEFAULT '0',
  `purity` varchar(10) NOT NULL,
  `ramt` varchar(11) NOT NULL,
  `branchId` varchar(8) NOT NULL,
  `agent_id` varchar(100) NOT NULL,
  `followUp` varchar(10) NOT NULL,
  `comment` varchar(500) NOT NULL DEFAULT '',
  `remarks` varchar(300) NOT NULL DEFAULT '',
  `zonal_remarks` varchar(200) NOT NULL DEFAULT '',
  `status` int(1) NOT NULL,
  `emp_type` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `indate` varchar(10) NOT NULL,
  `time` time NOT NULL,
  `quotation` varchar(255) NOT NULL,
  `bills` varchar(10) NOT NULL,
  `quot_rate` varchar(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `asset`
--
ALTER TABLE `asset`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branchId` (`branchId`),
  ADD KEY `empId` (`empId`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `bankdetails`
--
ALTER TABLE `bankdetails`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `billID` (`billID`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `branch`
--
ALTER TABLE `branch`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `closing`
--
ALTER TABLE `closing`
  ADD PRIMARY KEY (`closingID`),
  ADD KEY `date` (`date`),
  ADD KEY `branchId` (`branchId`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mobile` (`mobile`);

--
-- Indexes for table `customerinfo`
--
ALTER TABLE `customerinfo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_data`
--
ALTER TABLE `customer_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enquiry`
--
ALTER TABLE `enquiry`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `everycustomer`
--
ALTER TABLE `everycustomer`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `branch` (`branch`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `expense`
--
ALTER TABLE `expense`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branchCode` (`branchCode`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `fraud`
--
ALTER TABLE `fraud`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fund`
--
ALTER TABLE `fund`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch` (`branch`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `gold`
--
ALTER TABLE `gold`
  ADD PRIMARY KEY (`id`),
  ADD KEY `city` (`city`),
  ADD KEY `type` (`type`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `it_issue`
--
ALTER TABLE `it_issue`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `job`
--
ALTER TABLE `job`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `language`
--
ALTER TABLE `language`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loginotp`
--
ALTER TABLE `loginotp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch` (`branch`),
  ADD KEY `empid` (`empid`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mails`
--
ALTER TABLE `mails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `toBranch` (`toBranch`);

--
-- Indexes for table `misc`
--
ALTER TABLE `misc`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ornament`
--
ALTER TABLE `ornament`
  ADD PRIMARY KEY (`ornamentId`),
  ADD KEY `billId` (`billId`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `otp`
--
ALTER TABLE `otp`
  ADD PRIMARY KEY (`otpid`);

--
-- Indexes for table `pledge_bill`
--
ALTER TABLE `pledge_bill`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoiceId` (`invoiceId`),
  ADD UNIQUE KEY `billId` (`billId`);

--
-- Indexes for table `pledge_fund`
--
ALTER TABLE `pledge_fund`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pledge_ornament`
--
ALTER TABLE `pledge_ornament`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quiz_branch_data`
--
ALTER TABLE `quiz_branch_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quiz_schedule`
--
ALTER TABLE `quiz_schedule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `releasedata`
--
ALTER TABLE `releasedata`
  ADD PRIMARY KEY (`rid`),
  ADD KEY `BranchId` (`BranchId`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `renewal`
--
ALTER TABLE `renewal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trans`
--
ALTER TABLE `trans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branchId` (`branchId`),
  ADD KEY `date` (`date`),
  ADD KEY `phone` (`phone`);

--
-- Indexes for table `trare`
--
ALTER TABLE `trare`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branchTo` (`branchTo`),
  ADD KEY `branchId` (`branchId`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vmagent`
--
ALTER TABLE `vmagent`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vm_log`
--
ALTER TABLE `vm_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `walkin`
--
ALTER TABLE `walkin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branchId` (`branchId`),
  ADD KEY `date` (`date`),
  ADD KEY `issue` (`issue`),
  ADD KEY `mobile` (`mobile`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `asset`
--
ALTER TABLE `asset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bankdetails`
--
ALTER TABLE `bankdetails`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branch`
--
ALTER TABLE `branch`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `closing`
--
ALTER TABLE `closing`
  MODIFY `closingID` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customerinfo`
--
ALTER TABLE `customerinfo`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_data`
--
ALTER TABLE `customer_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enquiry`
--
ALTER TABLE `enquiry`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `everycustomer`
--
ALTER TABLE `everycustomer`
  MODIFY `Id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expense`
--
ALTER TABLE `expense`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fraud`
--
ALTER TABLE `fraud`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fund`
--
ALTER TABLE `fund`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gold`
--
ALTER TABLE `gold`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `it_issue`
--
ALTER TABLE `it_issue`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job`
--
ALTER TABLE `job`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `language`
--
ALTER TABLE `language`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loginotp`
--
ALTER TABLE `loginotp`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mails`
--
ALTER TABLE `mails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `misc`
--
ALTER TABLE `misc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ornament`
--
ALTER TABLE `ornament`
  MODIFY `ornamentId` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otp`
--
ALTER TABLE `otp`
  MODIFY `otpid` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pledge_bill`
--
ALTER TABLE `pledge_bill`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pledge_fund`
--
ALTER TABLE `pledge_fund`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pledge_ornament`
--
ALTER TABLE `pledge_ornament`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_branch_data`
--
ALTER TABLE `quiz_branch_data`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_schedule`
--
ALTER TABLE `quiz_schedule`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `releasedata`
--
ALTER TABLE `releasedata`
  MODIFY `rid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `renewal`
--
ALTER TABLE `renewal`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trans`
--
ALTER TABLE `trans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trare`
--
ALTER TABLE `trare`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vmagent`
--
ALTER TABLE `vmagent`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vm_log`
--
ALTER TABLE `vm_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `walkin`
--
ALTER TABLE `walkin`
  MODIFY `id` int(7) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
