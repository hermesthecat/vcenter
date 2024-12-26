<!--
 * @Descripttion: 
 * @version: 
 * @Author: Abdullah GÖK (abdullahazad)
 * @Date: 2024-12-26 17:29:28
 * @LastEditors: Abdullah GÖK (abdullahazad)
 * @LastEditTime: 2024-12-26 17:32:09
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

### Teknolojiler
- PHP (Backend)
- JavaScript (Frontend)
- Chart.js (Grafikler için)
- SweetAlert2 (Bildirimler için)
- REST API (vCenter ile iletişim için)

### Dosya Yapısı
- `index.php`: Ana sayfa, VM listesi ve oluşturma modalı
- `view.php`: VM detayları ve performans grafikleri
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

### Technologies
- PHP (Backend)
- JavaScript (Frontend)
- Chart.js (For graphs)
- SweetAlert2 (For notifications)
- REST API (For vCenter communication)

### File Structure
- `index.php`: Main page, VM list and creation modal
- `view.php`: VM details and performance graphs
- `backend.php`: vCenter API operations and AJAX requests
- `vm_form.php`: VM creation form fields
- `app.js`: All JavaScript functions
- `style.css`: All CSS styles 