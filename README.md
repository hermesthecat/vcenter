<!--
 * @Descripttion: 
 * @version: 
 * @Author: Abdullah GÖK (abdullahazad)
 * @Date: 2024-12-26 17:29:28
 * @LastEditors: Abdullah GÖK (abdullahazad)
 * @LastEditTime: 2024-12-26 17:50:05
-->
# vCenter VM Management Interface

## 🇹🇷 Türkçe

### Proje Açıklaması
Bu proje, VMware vCenter sunucusu üzerinde sanal makinelerin (VM) yönetimini sağlayan web tabanlı bir arayüz sunar. Kullanıcılar bu arayüz sayesinde:

- Mevcut sanal makineleri görüntüleyebilir
- Şablonlardan yeni sanal makineler oluşturabilir
- VM'lerin detaylı istatistiklerini inceleyebilir
- VM'lerin performans metriklerini grafiksel olarak takip edebilir
- VM ile ilgili son 50 uyarıyı görüntüleyebilir
- VM'lerin snapshot'larını yönetebilir (en fazla 5 snapshot)

### Özellikler
- **VM Listesi**: Tüm sanal makinelerin güç durumu, CPU sayısı ve bellek boyutu gibi temel bilgilerini gösterir
- **Şablon Listesi**: Kullanılabilir VM şablonlarını listeler
- **VM Oluşturma**: 
  - Şablondan yeni VM oluşturma
  - CPU, RAM, disk boyutu ayarlama
  - Ağ ve depolama politikası seçimi
  - Kaynak havuzu ve küme seçimi
- **VM Detay Sayfası**:
  - CPU, RAM, disk ve ağ kullanım grafikleri
  - VMDK dosya yolları
  - VM ile ilgili son uyarılar
  - Snapshot yönetimi (oluşturma, listeleme)
- **Snapshot Yönetimi**:
  - VM için en fazla 5 snapshot oluşturma
  - Snapshot adı ve açıklama ekleme
  - Bellek durumunu dahil etme seçeneği
  - Dosya sistemi dondurma (quiesce) seçeneği
  - Mevcut snapshot'ları listeleme

### Teknolojiler
- PHP (Backend)
- JavaScript (Frontend)
- Chart.js (Grafikler için)
- SweetAlert2 (Bildirimler için)
- REST API (vCenter ile iletişim için)

### Dosya Yapısı
- `index.php`: Ana sayfa, VM listesi ve oluşturma modalı
- `view.php`: VM detayları, performans grafikleri ve snapshot yönetimi
- `backend.php`: vCenter API işlemleri ve AJAX istekleri
- `vm_form.php`: VM oluşturma form alanları
- `app.js`: Tüm JavaScript fonksiyonları
- `style.css`: Tüm CSS stilleri

---

## 🇬🇧 English

### Project Description
This project provides a web-based interface for managing virtual machines (VMs) on a VMware vCenter server. Through this interface, users can:

- View existing virtual machines
- Create new VMs from templates
- Examine detailed VM statistics
- Monitor VM performance metrics graphically
- View the last 50 alerts related to a VM
- Manage VM snapshots (maximum 5 snapshots)

### Features
- **VM List**: Shows basic information such as power state, CPU count, and memory size of all VMs
- **Template List**: Lists available VM templates
- **VM Creation**: 
  - Create new VM from template
  - Configure CPU, RAM, disk size
  - Select network and storage policy
  - Choose resource pool and cluster
- **VM Detail Page**:
  - CPU, RAM, disk, and network usage graphs
  - VMDK file paths
  - Recent VM alerts
  - Snapshot management (create, list)
- **Snapshot Management**:
  - Create up to 5 snapshots per VM
  - Add snapshot name and description
  - Option to include memory state
  - Option to quiesce file system
  - List existing snapshots

### Technologies
- PHP (Backend)
- JavaScript (Frontend)
- Chart.js (For graphs)
- SweetAlert2 (For notifications)
- REST API (For vCenter communication)

### File Structure
- `index.php`: Main page, VM list and creation modal
- `view.php`: VM details, performance graphs and snapshot management
- `backend.php`: vCenter API operations and AJAX requests
- `vm_form.php`: VM creation form fields
- `app.js`: All JavaScript functions
- `style.css`: All CSS styles 